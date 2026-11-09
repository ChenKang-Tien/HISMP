<template>
    <div v-if="modelValue" class="modal-mask" @click.self="close">
        <div class="modal-container">
            <div class="modal-header">
                <span class="header-title"><i class="ti ti-package"></i> 明日庫房領料大總表</span>
                <button class="close-x" @click="close" title="關閉">×</button>
            </div>

            <div class="modal-body">
                <div class="meta-desc">
                    <span>💡</span>
                    <div>依明日預約病患的透析器醫囑精算（短期優先，無短期取長期），點擊品項可查看配給病患名單</div>
                </div>

                <div v-for="item in supplies" :key="item.id" class="supply-block">
                    <div class="supply-row" @click="toggle(item.id)">
                        <span class="supply-name"><i class="ti ti-package"></i>{{ item.name }}</span>
                        <span class="supply-count">{{ item.count }} {{ item.unit }} ℹ️</span>
                    </div>
                    <div v-if="expandedId === item.id" class="patient-list">
                        <div v-for="(p, i) in item.patients" :key="i" class="patient-line">
                            <span>{{ p.shift }}班 · {{ p.bed }} 床 · {{ p.name }}</span>
                            <span class="src-tag">{{ p.source }}醫囑</span>
                        </div>
                    </div>
                </div>
                <div v-if="!supplies.length" class="empty-text">明日沒有需領用的透析器</div>
            </div>

            <div class="modal-footer">
                <button class="btn-confirm-close" @click="close">關閉</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
const store = useDialysisStore();

const supplies = ref([]);
const expandedId = ref(null);

const load = async () => {
    const data = await store.fetchSupplyList();
    supplies.value = data?.items ?? [];
    expandedId.value = null;
};

watch(() => props.modelValue, (open) => { if (open) load(); }, { immediate: true });

const toggle = (id) => { expandedId.value = expandedId.value === id ? null : id; };
const close = () => emit('update:modelValue', false);

</script>

<style scoped>
.modal-mask { position: fixed; z-index: 9998; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; }
.modal-container { width: 94%; max-width: 580px; background: white; border-radius: 10px; box-shadow: 0 16px 40px rgba(15,23,42,.25); border: 1.5px solid #99f6e4; overflow: hidden; max-height: 90vh; display: flex; flex-direction: column; }
.modal-header { padding: 11px 16px; border-bottom: 1px solid #ccfbf1; background: #f0fdfa; color: #0f766e; display: flex; justify-content: space-between; align-items: center; }
.header-title { font-size: 14.5px; font-weight: 700; display: flex; align-items: center; gap: 6px; }
.close-x { background: none; border: none; color: #0f766e; font-size: 18px; font-weight: bold; cursor: pointer; padding: 0 4px; line-height: 1; }
.modal-body { padding: 14px 16px; overflow-y: auto; font-size: 13px; flex: 1; }
.meta-desc { color: #475569; background: #f8fafc; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; border: 1px solid #e2e8f0; font-size: 12px; display: flex; align-items: flex-start; gap: 6px; }
.supply-block { margin-bottom: 6px; }
.supply-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 7px; cursor: pointer; }
.supply-row:hover { background: #ccfbf1; }
.supply-name { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: #134e4a; font-weight: 600; }
.supply-name i { color: #0f766e; }
.supply-count { background: #0f766e; color: white; font-size: 11.5px; font-weight: 700; padding: 2px 10px; border-radius: 10px; }
.patient-list { margin: 4px 0 0 14px; border-left: 2px solid #99f6e4; padding-left: 10px; }
.patient-line { display: flex; justify-content: space-between; font-size: 12px; color: #475569; padding: 3px 0; }
.src-tag { font-size: 10.5px; color: #0f766e; background: #ccfbf1; padding: 0 6px; border-radius: 8px; }
.empty-text { font-size: 11.5px; color: #94a3b8; text-align: center; padding: 8px 0; }
.modal-footer { padding: 10px 16px; border-top: 1px solid #ccfbf1; display: flex; justify-content: flex-end; gap: 8px; }
.btn-confirm-close { background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 14px; font-size: 12.5px; cursor: pointer; color: #475569; font-weight: 600; }
</style>

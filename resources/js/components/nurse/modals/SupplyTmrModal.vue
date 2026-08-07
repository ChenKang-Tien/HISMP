<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr"><i class="ti ti-package"></i>明日庫房領料大總表</div>
            <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            <div style="font-size:11px;color:var(--slate-lt);margin-bottom:8px">系統自動精算，點擊數量可查看配給病患名單</div>
            <div v-for="item in supplies" :key="item.id" style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--slate-ul);border-radius:7px;margin-bottom:4px;cursor:pointer" class="supply-item-row">
                <span style="display:flex;align-items:center;gap:7px;font-size:12px"><i class="ti ti-package" style="color:var(--teal)"></i>{{ item.name }}</span>
                <span style="background:var(--teal);color:white;font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px">{{ item.count }} {{ item.unit }} ℹ️</span>
            </div>
            <div style="background:var(--amber-lt);border:1px solid var(--amber-bd);border-radius:6px;padding:6px 10px;font-size:11px;color:#92400e;margin-top:8px">⚠️ 鎖定後若醫師修改明日醫囑，隔日白班登入時系統將跳出黃色告警</div>
            <div class="mbtn-row">
                <button class="mbtn sec" @click="$emit('update:modelValue', false)">關閉</button>
                <button class="mbtn pri" @click="lockList" :disabled="isLocked" :style="{ opacity: isLocked ? .5 : 1, cursor: isLocked ? 'not-allowed' : 'pointer' }">
                    {{ isLocked ? '🔒 已鎖定' : '🔒 具名鎖定領料清單' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
const store = useDialysisStore();

const supplies = ref([]);
const isLocked = ref(false);

onMounted(async () => {
    const data = await store.fetchSupplyList();
    supplies.value = data.items;
    isLocked.value = data.isLocked;
});

const lockList = async () => {
    const success = await store.lockSupplyList();
    if(success) isLocked.value = true;
};
</script>

<style scoped>
.modal-overlay { display: flex; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 500; align-items: center; justify-content: center; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-x { position: absolute; top: 12px; right: 12px; background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
</style>

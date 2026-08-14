<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal" style="max-width:420px">
            <div class="modal-hdr"><i class="ti ti-bolt"></i>⚡ 臨床突發事件</div>
            <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            <div style="font-size:11px;color:var(--slate);margin-bottom:10px;">請選擇突發事件類型：</div>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;">
                <div v-for="evt in events" :key="evt.type" 
                     @click="submit(evt.type)"
                     style="border:1.5px solid var(--border);border-radius:8px;padding:10px 12px;cursor:pointer;display:flex;align-items:center;gap:10px;background:white;" 
                     onmouseover="this.style.background='var(--teal-lt)'" 
                     onmouseout="this.style.background='white'">
                    <span style="font-size:18px;">{{ evt.icon }}</span>
                    <div>
                        <div style="font-weight:700;font-size:12px;">{{ evt.label }}</div>
                        <div style="font-size:10px;color:var(--slate-lt)">{{ evt.desc }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps(['modelValue', 'patient']);
const emit = defineEmits(['update:modelValue']);
const store = useDialysisStore();

const events = [
    { type: 'BED_CHANGE', icon: '🛏️', label: '換床', desc: '病患移至其他透析床位，IoT 數據重新綁定' },
    { type: 'FALL', icon: '⚠️', label: '跌倒', desc: '病患發生跌倒事件，需通報醫療事故' },
    { type: 'BLOOD_LEAK', icon: '🩸', label: '漏血', desc: '發生滲漏血情況，已即時處理' }
];

const submit = async (type) => {
    const success = await store.reportIncident(store.currentPatient.mr, type);
    if (success) {
        emit('update:modelValue', false);
    }
};
</script>

<style scoped>
.modal-overlay { display: flex; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 500; align-items: center; justify-content: center; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-x { position: absolute; top: 12px; right: 12px; background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
</style>

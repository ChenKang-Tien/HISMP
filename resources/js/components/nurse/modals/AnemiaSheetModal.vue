<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-droplet"></i>🩸 貧血治療</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            <div class="modal-body" style="font-size:13.5px;">
                <button class="print-btn-inline" style="margin-bottom:10px;" @click="handlePrint">🖨️ 列印</button>
                <div style="font-weight:700;color:#334155;margin-bottom:10px;font-size:13.5px;">HCT 歷史記錄（最多六週）</div>
                
                <!-- 週別切換 (DL-084) -->
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <button
                        class="week-nav-btn"
                        :disabled="currentWeekIdx <= 0"
                        @click="currentWeekIdx--"
                    >
                        ◀
                    </button>
                    <span style="font-size:14px;font-weight:700;color:#0f766e;">
                        {{ currentWeekItem.week }}
                    </span>
                    <button
                        class="week-nav-btn"
                        :disabled="currentWeekIdx >= hctHistory.length - 1"
                        @click="currentWeekIdx++"
                    >
                        ▶
                    </button>
                </div>

                <!-- LW / TW 雙卡顯示 (DL-084) -->
                <div style="display:flex;gap:12px;margin-bottom:16px;">
                    <div class="hct-card">
                        <div class="hct-card-lbl">LW (上週)</div>
                        <div class="hct-card-val">{{ currentWeekItem.lw || '—' }}</div>
                    </div>
                    <div class="hct-card">
                        <div class="hct-card-lbl">TW (本週)</div>
                        <div class="hct-card-val">{{ currentWeekItem.tw || currentWeekItem.value || '—' }}</div>
                    </div>
                </div>

                <!-- EPO 施打記錄 -->
                <div class="epo-section">
                    <div class="epo-title">EPO 施打記錄</div>
                    <div class="epo-content">
                        {{ detailData?.epo_record || 'NESP N20（4000IU）｜ W一・W三 施打｜ 最近施打：2026-05-26' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps(['modelValue', 'patient', 'detailData']);
defineEmits(['update:modelValue']);

const defaultHistory = [
    { week: '4月第3週', lw: '29.5', tw: '31.0', value: '31.0' },
    { week: '4月第4週', lw: '30.0', tw: '32.0', value: '32.0' },
    { week: '5月第1週', lw: '29.8', tw: '31.5', value: '31.5' },
    { week: '5月第2週', lw: '30.0', tw: '32.5', value: '32.5' },
];

const hctHistory = computed(() => {
    return props.detailData?.hct_history || defaultHistory;
});

const currentWeekIdx = ref(3);

const currentWeekItem = computed(() => {
    if (!hctHistory.value || hctHistory.value.length === 0) {
        return { week: '尚無記錄', lw: '—', tw: '—' };
    }
    const idx = Math.min(Math.max(0, currentWeekIdx.value), hctHistory.value.length - 1);
    return hctHistory.value[idx];
});

const handlePrint = () => {
    window.print();
};
</script>

<style scoped>
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex !important; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-hdr { background: #0f766e; color: white; padding: 10px 14px; font-size: 13px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; margin: -16px -16px 16px -16px; border-radius: 13px 13px 0 0; }
.modal-x { background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
.print-btn-inline { font-size: 11px; background: #e2e8f0; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-weight: 600; color: #334155; }
.print-btn-inline:hover { background: #cbd5e1; }
.week-nav-btn { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 10px; cursor: pointer; font-size: 12px; color: #334155; transition: background 0.15s; }
.week-nav-btn:hover:not(:disabled) { background: #e2e8f0; }
.week-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.hct-card { flex: 1; background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 7px; padding: 10px; text-align: center; }
.hct-card-lbl { font-size: 11px; color: #64748b; font-weight: 600; margin-bottom: 4px; }
.hct-card-val { font-size: 18px; font-weight: 700; color: #0f766e; }
.epo-section { border-top: 1px solid #e2e8f0; padding-top: 10px; }
.epo-title { font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 6px; }
.epo-content { font-size: 11px; color: #1e293b; line-height: 1.6; }
</style>

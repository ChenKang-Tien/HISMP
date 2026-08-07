<template>
    <div v-if="modelValue" class="modal-overlay" :class="{ open: modelValue }" @click.self="close">
        <div class="modal-box">
            <div class="modal-hdr">
                <span><i class="ti ti-file-medical"></i>📋 透析記錄單</span>
                <button class="close-x" @click="close">✕</button>
            </div>
            
            <button class="print-btn-inline">🖨️ 列印</button>
            
            <div class="modal-body">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <span style="font-size:11px;color:var(--slate)">查詢日期：</span>
                    <input type="date" v-model="selectedDate" class="modal-input">
                </div>
                
                <div style="border:1.5px solid var(--border);border-radius:8px;padding:10px;font-size:11px;line-height:1.8">
                    <div style="font-weight:700;text-align:center;font-size:13px;border-bottom:1.5px solid var(--border);padding-bottom:5px;margin-bottom:8px">泰安診所 血液透析治療記錄單</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-bottom:8px">
                        <div>病患：{{ patient?.name }}（{{ patient?.mr }}）</div>
                        <div>日期：{{ selectedDate }}</div>
                        <div>Dialyzer：{{ detailData?.dialyzer || '—' }}</div>
                        <div>BF：{{ detailData?.bf || '—' }} ml/min</div>
                        <div>DF：{{ detailData?.df || '—' }} ml/min</div>
                        <div>Ca：{{ detailData?.ca || '—' }} mEq/L</div>
                        <div>透前體重：{{ detailData?.pre_weight || '—' }} kg</div>
                        <div>UF目標：{{ detailData?.uf_goal || '—' }} kg</div>
                        <div>On-Sign：{{ detailData?.on_sign_nurse || '—' }}</div>
                        <div>Double Sign：{{ detailData?.double_sign_nurse || '—' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">關閉</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps(['modelValue', 'patient', 'detailData']);
const emit = defineEmits(['update:modelValue']);
const store = useDialysisStore();

const close = () => emit('update:modelValue', false);
const selectedDate = ref(new Date().toISOString().split('T')[0]);

// 監聽日期變更，可擴充後端查詢
watch(selectedDate, (newDate) => {
    console.log(`[DialysisRecord] 查詢 ${newDate} 資料`);
});
</script>

<style scoped>
/* 使用全域通用 modal 樣式 */
.print-btn-inline {
    margin: 0 14px;
    font-size: 11px;
    background: #e2e8f0;
    border: none;
    padding: 2px 8px;
    border-radius: 4px;
    cursor: pointer;
}
</style>

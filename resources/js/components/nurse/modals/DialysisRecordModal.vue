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
                        <div>Dialyzer：FX80 Classix</div>
                        <div>BF：250 ml/min</div>
                        <div>DF：500 ml/min</div>
                        <div>Ca：3.0 mEq/L</div>
                        <div>透前體重：78.5 kg</div>
                        <div>UF目標：3.50 kg</div>
                        <div>On-Sign：楚心瑜 09:05</div>
                        <div>Double Sign：王曉明 09:08</div>
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
import { ref, computed } from "vue";

const props = defineProps(['modelValue', 'patient']);
const emit = defineEmits(['update:modelValue']);

const close = () => emit('update:modelValue', false);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
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

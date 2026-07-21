<template>
    <BaseModal v-model="internalValue">
        <div class="modal-hdr"><i class="ti ti-file-medical"></i>📋 透析記錄單</div>
        <button class="print-btn-inline">🖨️ 列印</button>
        
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <span style="font-size:11px;color:var(--slate)">查詢日期：</span>
            <input type="date" v-model="selectedDate" style="font-size:11px;border:1.5px solid var(--border);border-radius:5px;padding:3px 6px;">
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
    </BaseModal>
</template>

<script setup>
import { ref, computed } from "vue";
import BaseModal from "./BaseModal.vue";

const props = defineProps(['modelValue', 'patient']);
const emit = defineEmits(['update:modelValue']);

const internalValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const selectedDate = ref(new Date().toISOString().split('T')[0]);
</script>

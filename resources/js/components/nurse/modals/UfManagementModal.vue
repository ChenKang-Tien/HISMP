<template>
    <BaseModal v-model="internalValue">
        <div class="modal-hdr">
            <i class="ti ti-droplet"></i> 調水量管理 (UF Management)
        </div>
        
        <div class="form-group" style="margin-bottom: 12px;">
            <label class="form-label">目標總調水量 (kg)</label>
            <input v-model="ufData.target" type="number" step="0.1" class="form-input" placeholder="例如: 3.5">
        </div>

        <div class="form-row">
            <div class="form-col">
                <label class="form-label">透析時間 (hr)</label>
                <input v-model="ufData.hours" type="number" class="form-input" placeholder="4">
            </div>
            <div class="form-col">
                <label class="form-label">流速 (ml/hr)</label>
                <input :value="calculatedRate" type="number" class="form-input" disabled>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">備註</label>
            <textarea v-model="ufData.note" class="form-input" rows="2" placeholder="調水原因..."></textarea>
        </div>

        <div class="mbtn-row">
            <button class="mbtn sec" @click="internalValue = false">取消</button>
            <button class="mbtn pri" @click="submit">確認設定</button>
        </div>
    </BaseModal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import BaseModal from "./BaseModal.vue";

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue', 'confirm']);

const internalValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const ufData = ref({
    target: 0,
    hours: 4,
    note: ''
});

const calculatedRate = computed(() => {
    if (ufData.value.hours > 0) {
        return (ufData.value.target * 1000 / ufData.value.hours).toFixed(0);
    }
    return 0;
});

const submit = () => {
    emit('confirm', { ...ufData.value, rate: calculatedRate.value });
    internalValue.value = false;
};
</script>

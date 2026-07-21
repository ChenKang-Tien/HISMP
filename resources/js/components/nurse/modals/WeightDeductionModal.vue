<template>
    <BaseModal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)">
        <div class="v24-modal-header">
            <span class="v24-modal-title">
                <i class="ti ti-settings"></i> 扣重品項膠囊管理池
            </span>
        </div>
        <div class="v24-modal-body">
            <div class="v24-section-label">現行套用扣重膠囊：</div>
            <div class="v24-pool-display">
                <div v-if="!store.deductions.length" class="v24-empty-text">
                    今日尚無套用扣重項目
                </div>
                <div v-else class="v24-pill-container">
                    <div v-for="d in store.deductions" :key="d.id" class="v24-applied-pill">
                        <span class="pill-name">{{ d.name }}</span>
                        <span class="pill-weight">-{{ d.weight.toFixed(1) }} kg</span>
                        <button class="pill-remove" @click="removeItem(d.id)">✕</button>
                    </div>
                </div>
            </div>
            <div class="v24-input-card">
                <div class="v24-input-row">
                    <div class="v24-field-group" style="flex: 2">
                        <label class="v24-label">品項名稱</label>
                        <input type="text" v-model="newItemName" placeholder="如：厚重棉襖" class="v24-input" />
                    </div>
                    <div class="v24-field-group" style="flex: 1">
                        <label class="v24-label">重量 (kg)</label>
                        <input type="text" inputmode="numeric" v-model="newItemWeightRaw" @input="handleWeightInput" placeholder="0.2" class="v24-input" />
                    </div>
                    <button class="v24-btn-add" @click="addItem">
                        <i class="ti ti-plus"></i> 新增
                    </button>
                </div>
            </div>
        </div>
        <div class="v24-modal-footer">
            <div class="v24-total-text">
                總扣重：<span class="font-bold">{{ store.deductionTotal.toFixed(1) }} kg</span>
            </div>
            <button class="v24-btn-close" @click="$emit('update:modelValue', false)">完成關閉</button>
        </div>
    </BaseModal>
</template>

<script setup>
import { ref } from "vue";
import BaseModal from './BaseModal.vue';
import { useDialysisStore } from "@/store/useNurseStore";

defineProps(['modelValue']);
defineEmits(['update:modelValue']);
const store = useDialysisStore();

const newItemName = ref("");
const newItemWeightRaw = ref("0.2");

const handleWeightInput = (e) => {
    newItemWeightRaw.value = String(e.target.value).replace(/[^0-9.]/g, "");
};

const addItem = () => {
    if (!newItemName.value.trim() || !newItemWeightRaw.value) return;
    store.deductions.push({
        id: Date.now(),
        name: newItemName.value.trim(),
        weight: parseFloat(newItemWeightRaw.value),
    });
    newItemName.value = "";
    newItemWeightRaw.value = "0.2";
};

const removeItem = (id) => {
    store.deductions = store.deductions.filter((d) => d.id !== id);
};
</script>

<style scoped>
.v24-modal-header { display: flex; justify-content: space-between; padding: 10px; background: #f0fdfa; color: #0f766e; border-radius: 8px; font-weight: 700; margin-bottom: 10px; }
.v24-modal-title { display: flex; align-items: center; gap: 5px; }
.v24-modal-body { padding: 10px; }
.v24-section-label { font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 5px; }
.v24-pool-display { background: #f1f5f9; border-radius: 6px; padding: 8px; min-height: 50px; }
.v24-pill-container { display: flex; flex-wrap: wrap; gap: 5px; }
.v24-applied-pill { background: white; border: 1.5px solid #99f6e4; padding: 3px 8px; border-radius: 12px; display: flex; align-items: center; gap: 5px; font-size: 11px; }
.pill-name { color: #0f766e; font-weight: 600; }
.pill-weight { color: #b45309; }
.pill-remove { cursor: pointer; color: #b91c1c; border: none; background: none; padding: 0 2px; }
.v24-input-card { margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
.v24-input-row { display: flex; gap: 8px; align-items: flex-end; }
.v24-field-group { display: flex; flex-direction: column; }
.v24-label { font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 2px; }
.v24-input { padding: 6px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 12px; }
.v24-btn-add { background: #0f766e; color: white; border: none; border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; }
.v24-modal-footer { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-top: 1px solid #e2e8f0; margin-top: 10px; }
.v24-total-text { font-size: 12px; font-weight: 700; }
.v24-btn-close { background: #f1f5f9; border: none; border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; }
</style>

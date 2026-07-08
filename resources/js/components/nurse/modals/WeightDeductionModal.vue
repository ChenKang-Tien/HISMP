<template>
    <div v-if="modelValue" class="v24-modal-mask" @click.self="closeModal">
        <div class="v24-modal-box">
            <div class="v24-modal-header">
                <span class="v24-modal-title">
                    <i class="ti ti-settings"></i> 扣重品項膠囊管理池
                </span>
                <button class="v24-modal-x" @click="closeModal">✕</button>
            </div>

            <div class="v24-modal-body">
                <div class="v24-section-label">現行套用扣重膠囊：</div>

                <div class="v24-pool-display">
                    <div v-if="!store.deductions.length" class="v24-empty-text">
                        今日尚無套用扣重項目
                    </div>
                    <div v-else class="v24-pill-container">
                        <div
                            v-for="d in store.deductions"
                            :key="d.id"
                            class="v24-applied-pill"
                        >
                            <span class="pill-name">{{ d.name }}</span>
                            <span class="pill-weight"
                                >-{{ d.weight.toFixed(1) }} kg</span
                            >
                            <button
                                class="pill-remove"
                                @click="removeItem(d.id)"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <div class="v24-input-card">
                    <div class="v24-input-row">
                        <div class="v24-field-group" style="flex: 2">
                            <label class="v24-label">品項名稱</label>
                            <input
                                type="text"
                                v-model="newItemName"
                                placeholder="如：厚重棉襖"
                                class="v24-input"
                            />
                        </div>

                        <div class="v24-field-group" style="flex: 1">
                            <label class="v24-label">重量 (kg)</label>
                            <input
                                type="text"
                                inputmode="numeric"
                                v-model="newItemWeightRaw"
                                @input="handleWeightInput"
                                placeholder="0.2"
                                class="v24-input"
                            />
                        </div>

                        <button class="v24-btn-add" @click="addItem">
                            <i class="ti ti-plus"></i> 新增
                        </button>
                    </div>
                </div>
            </div>

            <div class="v24-modal-footer">
                <div class="v24-total-text">
                    總扣重：<span class="font-bold"
                        >{{ store.deductionTotal.toFixed(1) }} kg</span
                    >
                </div>
                <button class="v24-btn-close" @click="closeModal">
                    完成關閉
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue"]);
const store = useDialysisStore();

const newItemName = ref("");
const newItemWeightRaw = ref("0.2");

const formatSmartDecimal = (raw) => {
    const d = String(raw).replace(/[^0-9.]/g, "");
    if (!d) return "";
    return d;
};

const handleWeightInput = (e) => {
    newItemWeightRaw.value = formatSmartDecimal(e.target.value);
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

const closeModal = () => {
    emit("update:modelValue", false);
};
</script>

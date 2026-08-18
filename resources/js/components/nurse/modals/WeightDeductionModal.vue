<template>
    <BaseModal
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <div class="v24-modal-header">
            <span class="v24-modal-title">
                <i class="ti ti-settings"></i> 扣重品項膠囊管理池
            </span>
        </div>
        <div class="v24-modal-body">
            <div class="v24-section-label">選擇項目類別：</div>
            <div class="v24-pool-display" style="margin-bottom: 10px">
                <label
                    ><input type="radio" v-model="targetCategory" value="pre" />
                    透前扣重</label
                >
                <label style="margin-left: 10px"
                    ><input
                        type="radio"
                        v-model="targetCategory"
                        value="post"
                    />
                    透後扣重</label
                >
            </div>

            <div class="v24-section-label">
                現行套用扣重膠囊 ({{
                    targetCategory === "pre" ? "透前" : "透後"
                }})：
                <button 
                    v-if="targetCategory === 'post'" 
                    class="v24-btn-add" 
                    style="margin-left: 10px; padding: 2px 6px; font-size: 10px;"
                    @click="confirmCopyPreItems"
                >
                    <i class="ti ti-copy"></i> 帶入透前項目
                </button>
            </div>
            <div class="v24-pool-display">
                <div v-if="!currentDeductions.length" class="v24-empty-text">
                    今日尚無套用扣重項目
                </div>
                <div v-else class="v24-pill-container">
                    <div
                        v-for="d in currentDeductions"
                        :key="d.id"
                        class="v24-applied-pill"
                    >
                        <span class="pill-name">{{ d.name }}</span>
                        <span class="pill-weight"
                            >{{ Number(d.weight) >= 0 ? '+' : '' }}{{ Number(d.weight).toFixed(1) }} kg</span
                        >
                        <button
                            class="pill-remove"
                            @click.stop.prevent="removeItem(d.id)"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            </div>
            <div class="v24-input-card">
                <div class="v24-input-row" style="margin-bottom: 8px">
                    <div class="v24-field-group" style="flex: 2">
                        <label class="v24-label">選擇項目</label>
                        <select v-model="selectedItem" class="v24-input">
                            <option value="" disabled>請選擇項目</option>
                            <option
                                v-for="it in weightAdjustItems"
                                :key="it.id"
                                :value="it"
                            >
                                {{ it.item }} (預設: {{ it.default_weight }}kg)
                            </option>
                        </select>
                    </div>
                </div>
                <div class="v24-input-row">
                    <div class="v24-field-group" style="flex: 2">
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
                    >{{
                        (
                            (targetCategory === "pre"
                                ? store.preDeductionTotal
                                : store.postDeductionTotal) || 0
                        ).toFixed(1)
                    }}
                    kg</span
                >
            </div>
            <button
                class="v24-btn-close"
                @click="$emit('update:modelValue', false)"
            >
                完成關閉
            </button>
        </div>
    </BaseModal>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import BaseModal from "./BaseModal.vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();
const api = store.api;

defineProps(["modelValue"]);
defineEmits(["update:modelValue"]);

const targetCategory = ref("pre");
const selectedItem = ref("");
const weightAdjustItems = ref([]);
const newItemWeightRaw = ref("0.2");

const currentDeductions = computed(() => {
    return targetCategory.value === "pre"
        ? store.preDeductions
        : store.postDeductions;
});

const reloadData = async () => {
    try {
        const res = await api.get("/weight-adjust-items");
        weightAdjustItems.value = res.data;
    } catch (e) {
        console.error("無法載入項目列表");
    }
};

onMounted(reloadData);

watch(selectedItem, (newVal) => {
    if (newVal) {
        newItemWeightRaw.value = String(newVal.default_weight);
    }
});

const handleWeightInput = (e) => {
    newItemWeightRaw.value = String(e.target.value).replace(/[^0-9.-]/g, "");
};

const addItem = async () => {
    if (!selectedItem.value || !newItemWeightRaw.value) return;
    
    // 強制明確抓取當前的類別
    const currentCat = targetCategory.value;
    
    const item = {
        id: Date.now(),
        item_id: selectedItem.value.id,
        name: selectedItem.value.item,
        weight: parseFloat(newItemWeightRaw.value),
        category: currentCat
    };

    console.log("Adding item to category:", currentCat, item);

    if (currentCat === "pre") {
        store.preDeductions.push(item);
    } else {
        store.postDeductions.push(item);
    }

    await store.syncWeightAdjustments(store.currentPatient.mr);

    store.calculateWeights();
    await reloadData();

    newItemWeightRaw.value = "0.2";
    selectedItem.value = "";
};

const confirmCopyPreItems = async () => {
    if (!confirm("確定要將透前調整項目複製到透後嗎？此操作將覆蓋現有透後項目並立即儲存。")) return;
    await copyPreItems();
};

const copyPreItems = async () => {
    store.postDeductions = store.preDeductions.map(d => ({
        ...d,
        id: Date.now() + Math.random(),
        category: 'post'
    }));
    await store.syncWeightAdjustments(store.currentPatient.mr);
    store.calculateWeights();
};

const removeItem = async (id) => {
    const currentCat = targetCategory.value;
    let newDeductions = (
        currentCat === "pre"
            ? [...store.preDeductions]
            : [...store.postDeductions]
    ).filter((d) => d.id !== id);

    if (currentCat === "pre") {
        store.preDeductions = newDeductions;
    } else {
        store.postDeductions = newDeductions;
    }

    await store.syncWeightAdjustments(store.currentPatient.mr);

    store.calculateWeights();
    await reloadData();
};
</script>

<style scoped>
.v24-modal-header {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #f0fdfa;
    color: #0f766e;
    border-radius: 8px;
    font-weight: 700;
    margin-bottom: 10px;
}
.v24-modal-title {
    display: flex;
    align-items: center;
    gap: 5px;
}
.v24-modal-body {
    padding: 10px;
}
.v24-section-label {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 5px;
}
.v24-pool-display {
    background: #f1f5f9;
    border-radius: 6px;
    padding: 8px;
    min-height: 50px;
}
.v24-pill-container {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.v24-applied-pill {
    background: white;
    border: 1.5px solid #99f6e4;
    padding: 3px 8px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
}
.pill-name {
    color: #0f766e;
    font-weight: 600;
}
.pill-weight {
    color: #b45309;
}
.pill-remove {
    cursor: pointer;
    color: #b91c1c;
    border: none;
    background: none;
    padding: 0 2px;
}
.v24-input-card {
    margin-top: 15px;
    border-top: 1px solid #e2e8f0;
    padding-top: 10px;
}
.v24-input-row {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}
.v24-field-group {
    display: flex;
    flex-direction: column;
}
.v24-label {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 2px;
}
.v24-input {
    padding: 6px;
    border: 1.5px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
}
.v24-btn-add {
    background: #0f766e;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
}
.v24-modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-top: 1px solid #e2e8f0;
    margin-top: 10px;
}
.v24-total-text {
    font-size: 12px;
    font-weight: 700;
}
.v24-btn-close {
    background: #f1f5f9;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
}
</style>

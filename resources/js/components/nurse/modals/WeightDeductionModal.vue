<template>
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box" style="max-width: 480px">
            <div class="modal-hdr">
                <span>⚙️ 扣重品項膠囊管理池</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <label style="display: block; margin-bottom: 4px"
                    >現行套用扣重膠囊：</label
                >
                <div
                    style="
                        display: flex;
                        flex-wrap: wrap;
                        gap: 6px;
                        margin-bottom: 12px;
                        background: #f8fafc;
                        padding: 8px;
                        border-radius: 6px;
                        min-height: 40px;
                    "
                >
                    <span
                        v-for="d in store.deductions"
                        :key="d.id"
                        style="
                            font-size: 11px;
                            background: #fffbeb;
                            border: 1px solid #fde68a;
                            border-radius: 20px;
                            padding: 2px 10px;
                            color: #b45309;
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                        "
                    >
                        {{ d.name }} ({{ d.weight }}kg)
                        <span style="font-size: 8px; color: #94a3b8"
                            >[改:{{ d.modifyCount || 0 }}次]</span
                        >
                        <button
                            @click="removeDeduction(d.id)"
                            style="
                                border: none;
                                background: transparent;
                                color: #ef4444;
                                font-weight: bold;
                                cursor: pointer;
                                font-size: 12px;
                            "
                        >
                            ×
                        </button>
                    </span>
                    <span
                        v-if="store.deductions.length === 0"
                        style="color: #94a3b8; font-size: 11px"
                        >今日尚無套用扣重項目</span
                    >
                </div>

                <div
                    style="
                        background: #f1f5f9;
                        padding: 10px;
                        border-radius: 6px;
                        display: flex;
                        gap: 6px;
                        align-items: flex-end;
                    "
                >
                    <div style="flex: 2">
                        <label
                            style="
                                font-size: 10px;
                                display: block;
                                color: #475569;
                            "
                            >品項名稱</label
                        >
                        <input
                            type="text"
                            v-model="newItem.name"
                            class="modal-input"
                            style="background: white; padding: 3px 6px"
                            placeholder="如: 厚重棉襖"
                        />
                    </div>
                    <div style="flex: 1">
                        <label
                            style="
                                font-size: 10px;
                                display: block;
                                color: #475569;
                            "
                            >重量 (kg)</label
                        >
                        <input
                            type="number"
                            step="0.1"
                            v-model.number="newItem.weight"
                            class="modal-input"
                            style="background: white; padding: 3px 6px"
                        />
                    </div>
                    <button
                        @click="addDeduction"
                        class="btn btn-teal"
                        style="padding: 4px 10px; font-size: 11px"
                        :disabled="!newItem.name || newItem.weight <= 0"
                    >
                        ➕ 新增
                    </button>
                </div>
            </div>
            <div class="modal-ftr">
                <span
                    style="
                        font-size: 11px;
                        color: #64748b;
                        margin-right: auto;
                        font-weight: 700;
                    "
                    >總扣重：{{ store.deductionTotal }} kg</span
                >
                <button class="btn btn-teal" @click="close">完成關閉</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue"]);

const store = useDialysisStore();
const newItem = reactive({ name: "", weight: 0.2 });

const close = () => emit("update:modelValue", false);

const addDeduction = () => {
    store.deductions.push({
        id: Date.now(),
        name: newItem.name,
        weight: newItem.weight,
        modifyCount: 0,
    });
    newItem.name = "";
    newItem.weight = 0.2;
};

const removeDeduction = (id) => {
    store.deductions = store.deductions.filter((d) => d.id !== id);
};
</script>

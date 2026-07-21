<template>
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box">
            <div class="modal-hdr">
                <span>🛡️ 新增臨床保護設備核定</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>勾選今日使用設備（多選）：</label>
                    <div
                        style="
                            display: flex;
                            flex-direction: column;
                            gap: 6px;
                            margin-top: 6px;
                        "
                    >
                        <label
                            v-for="item in options"
                            :key="item"
                            style="
                                display: flex;
                                align-items: center;
                                gap: 6px;
                                cursor: pointer;
                            "
                        >
                            <input
                                type="checkbox"
                                :value="item"
                                v-model="form.equipments"
                            />
                            {{ item }}
                        </label>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 12px">
                    <label>保護約束因由：</label>
                    <select v-model="form.reason" class="modal-input">
                        <option value="預防拔針 / 躁動風險">
                            預防拔針 / 躁動風險
                        </option>
                        <option value="透析中意識不清合作度佳">
                            透析中意識清楚但配合度欠佳
                        </option>
                        <option value="高跌倒風險交接">高跌倒風險交接</option>
                    </select>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button
                    class="btn btn-teal"
                    @click="submit"
                    :disabled="form.equipments.length === 0"
                >
                    確認套用並產生病歷
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";
const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue", "confirm"]);

const options = ["約束手套", "安全桌板", "胸腹部約束帶", "肢體約束帶"];
const form = reactive({ equipments: [], reason: "預防拔針 / 躁動風險" });

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            form.equipments = [];
            form.reason = "預防拔針 / 躁動風險";
        }
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    const text = `[保護設備] 今日啟用: ${form.equipments.join("、")}。約束原因：${form.reason}。`;
    emit("confirm", text);
    close();
};
</script>

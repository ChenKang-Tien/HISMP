<template>
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box">
            <div class="modal-hdr">
                <span>🩻 核發離院假單作業</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <p
                    style="
                        margin-bottom: 10px;
                        font-weight: bold;
                        color: #1e293b;
                    "
                >
                    病患：{{ patient?.name }} ({{ patient?.bed }}床 /
                    {{ patient?.mr }})
                </p>

                <div class="form-group">
                    <label>請選擇離院流轉狀態：</label>
                    <div style="display: flex; gap: 15px; margin-top: 5px">
                        <label
                            style="
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                gap: 4px;
                            "
                        >
                            <input
                                type="radio"
                                v-model="form.status"
                                value="LEAVE"
                            />
                            📋 病患請假
                        </label>
                        <label
                            style="
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                gap: 4px;
                            "
                        >
                            <input
                                type="radio"
                                v-model="form.status"
                                value="HOSPITALIZED"
                            />
                            🏥 轉院住院
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 12px">
                    <label>臨床主訴／交接備註：</label>
                    <textarea
                        v-model="form.note"
                        placeholder="請輸入病患請假主訴、住院醫院與交接因由..."
                        class="modal-input"
                        rows="3"
                    ></textarea>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button
                    class="btn btn-teal"
                    @click="submit"
                    :disabled="!form.status"
                >
                    具名核準假單
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
useTemplateRefs_Array: false;
import { ref, reactive, watch } from "vue";

const props = defineProps({
    modelValue: Boolean,
    patient: Object,
});
const emit = defineEmits(["update:modelValue", "confirm"]);

const form = reactive({ status: "LEAVE", note: "" });

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            form.status = "LEAVE";
            form.note = "";
        }
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    emit("confirm", { ...form });
    close();
};
</script>

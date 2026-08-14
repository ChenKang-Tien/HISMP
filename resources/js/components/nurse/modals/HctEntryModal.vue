<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="close">
        <div class="modal-box" style="max-width: 320px">
            <div class="modal-hdr">
                <span>🩸 HCT 量測值輸入 — TW</span>
                <button class="close-x" @click="close">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>本次 HCT 數值 (%)：</label>
                    <input
                        type="number"
                        v-model="val"
                        class="modal-input"
                        step="0.1"
                        placeholder="請輸入數值..."
                    />
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button class="btn btn-teal" @click="submit">儲存</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
const props = defineProps({
    modelValue: Boolean,
    initialValue: [String, Number],
    patient: Object,
    detailData: Object
});
const emit = defineEmits(["update:modelValue", "confirm"]);

const val = ref("");
watch(
    () => props.modelValue,
    (v) => {
        if (v) val.value = props.initialValue || "";
    },
);

import { useDialysisStore } from "@/store/useNurseStore";
const store = useDialysisStore();

const close = () => emit("update:modelValue", false);
const submit = async () => {
    const success = await store.addNursingRecord(`[HCT記錄] 設定 HCT 為 ${val.value}%`);
    if(success) {
        emit("confirm", val.value);
        close();
    }
};
</script>

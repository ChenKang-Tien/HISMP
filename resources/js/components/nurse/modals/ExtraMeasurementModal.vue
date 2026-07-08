<template>
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box" style="max-width: 440px">
            <div class="modal-hdr">
                <span>📊 新增時段臨時動態加測行</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>預計追蹤加測時間：</label>
                    <input
                        type="time"
                        v-model="form.target_time"
                        class="modal-input"
                    />
                </div>
                <div class="form-group" style="margin-top: 10px">
                    <label>臨時追加監測因由：</label>
                    <select v-model="form.reason" class="modal-input">
                        <option value="臨床突發低血壓 (BP < 90) 追蹤">
                            臨床突發低血壓 (BP < 90) 追蹤
                        </option>
                        <option value="病患主訴畏寒、發冷發熱加測">
                            病患主訴畏寒、發冷發熱加測
                        </option>
                        <option value="UFR調降後透析耐受度追蹤">
                            UFR調降後透析耐受度追蹤
                        </option>
                    </select>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button class="btn btn-teal" @click="submit">
                    在監控網格插入臨時行
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";
const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue", "confirm"]);

const form = reactive({
    target_time: "",
    reason: "臨床突發低血壓 (BP < 90) 追蹤",
});

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            const now = new Date();
            form.target_time = now.toTimeString().slice(0, 5);
        }
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    emit("confirm", { ...form });
    close();
};
</script>

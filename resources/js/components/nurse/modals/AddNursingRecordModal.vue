<template>
    <div :class="['modal-overlay', { open: modelValue }]" @click.self="close">
        <div class="modal-box" style="max-width: 500px">
            <div class="modal-hdr">
                <span>📝 新增具名護理記錄 (Progress Notes)</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label>時間：</label>
                    <input type="time" v-model="time" class="modal-input" />
                </div>
                <div class="form-group">
                    <label>內容：</label>
                    <textarea
                        v-model="content"
                        class="modal-input"
                        rows="5"
                        placeholder="請輸入處置病歷..."
                        @keydown.enter.ctrl="submit"
                    ></textarea>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button
                    class="btn btn-teal"
                    @click="submit"
                    :disabled="!content.trim()"
                >
                    具名寫入核心病歷
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue", "confirm"]);

const content = ref("");
const time = ref("");

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            content.value = "";
            time.value = new Date().toLocaleTimeString("zh-TW", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            });
        }
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    if (!content.value.trim()) return;
    emit("confirm", { content: content.value.trim(), time: time.value });
    close();
};
</script>

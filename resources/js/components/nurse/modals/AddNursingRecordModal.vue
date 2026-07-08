<template>
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box" style="max-width: 500px">
            <div class="modal-hdr">
                <span>📝 新增具名護理記錄 (Progress Notes)</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label
                        >請輸入臨床護理處置描述 (5W1H 稽核鏈自動追蹤)：</label
                    >
                    <textarea
                        v-model="content"
                        class="modal-input"
                        rows="5"
                        placeholder="請輸入處置病歷... (支援快捷輸入法)"
                        @keydown.enter.ctrl="submit"
                    ></textarea>
                    <span
                        style="
                            font-size: 9px;
                            color: #94a3b8;
                            display: block;
                            margin-top: 4px;
                        "
                        >提示：按 Ctrl + Enter 可快速送出儲存</span
                    >
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
watch(
    () => props.modelValue,
    (val) => {
        if (val) content.value = "";
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    if (!content.value.trim()) return;
    emit("confirm", content.value.trim());
    close();
};
</script>

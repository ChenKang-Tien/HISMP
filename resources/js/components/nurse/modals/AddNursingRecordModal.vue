<template>
    <div :class="['modal-overlay', { open: modelValue }]" @click.self="close">
        <div class="modal-box" style="max-width: 500px">
            <div class="modal-hdr">
                <span>📝 新增具名護理記錄 (Progress Notes)</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                    <div class="form-group">
                        <label>時間：</label>
                        <input type="time" v-model="time" class="modal-input" />
                    </div>
                    <div class="form-group">
                        <label>記錄護理師：</label>
                        <input
                            type="text"
                            v-model="nurse"
                            class="modal-input"
                            placeholder="護理師姓名"
                        />
                    </div>
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
                    具名寫入核心病歷 ({{ nurse || store.nurseName }})
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();
const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue", "confirm"]);

const content = ref("");
const time = ref("");
const nurse = ref(
    sessionStorage.getItem("hismp_user_name") || store.nurseName || "護理師",
);

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
            nurse.value =
                sessionStorage.getItem("hismp_user_name") ||
                store.nurseName ||
                "護理師";
        }
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    if (!content.value.trim()) return;
    const currentNurse =
        nurse.value.trim() ||
        sessionStorage.getItem("hismp_user_name") ||
        store.nurseName ||
        "護理師";
    emit("confirm", {
        content: content.value.trim(),
        time: time.value,
        nurse: currentNurse,
    });
    close();
};
</script>

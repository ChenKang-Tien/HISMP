<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="close">
        <div class="modal-box">
            <div class="modal-hdr">
                <span>📷 新增 Nurse Watching 影像事件</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>選擇視訊源鏡頭：</label>
                    <select v-model="form.source" class="modal-input">
                        <option value="廔管與針頭常駐相機 A">
                            廔管與針頭常駐相機 A
                        </option>
                        <option value="透析機面板 IoT 自動存取 B">
                            透析機面板 IoT 自動存取 B
                        </option>
                    </select>
                </div>
                <div
                    style="
                        margin: 10px 0;
                        background: #f1f5f9;
                        border-radius: 6px;
                        padding: 20px;
                        text-align: center;
                        color: #64748b;
                        font-size: 11px;
                        border: 1.5px dashed #cbd5e1;
                    "
                >
                    🖼️ [IoT 實時視訊串流截圖載入成功 - 穿刺點無外溢滲血快照]
                </div>
                <div class="form-group">
                    <label>臨床影像備註說明：</label>
                    <input
                        type="text"
                        v-model="form.note"
                        class="modal-input"
                        placeholder="例如：漏血偵測器異常排除、穿刺防護覆核..."
                    />
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button class="btn btn-teal" @click="submit">
                    綁定快照至護理記錄
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";
const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue", "confirm"]);

const form = reactive({ source: "廔管與針頭常駐相機 A", note: "" });
watch(
    () => props.modelValue,
    (v) => {
        if (v) form.note = "";
    },
);

const close = () => emit("update:modelValue", false);
const submit = () => {
    const text = `[Nurse Watching 📷] 串流來源: ${form.source}。快照備註: ${form.note || "無漏血異常排除覆核完成。"}`;
    emit("confirm", text);
    close();
};
</script>

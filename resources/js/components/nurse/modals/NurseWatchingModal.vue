<template>
<div v-if="modelValue" class="modal-overlay" @click.self="close">
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
    </div></input></div></div></div></div>
</template>

<script setup>
defineProps(['modelValue']);
defineEmits(['update:modelValue']);
</script>

<style scoped>
.modal-overlay { display: flex; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 500; align-items: center; justify-content: center; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-x { position: absolute; top: 12px; right: 12px; background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
</style>

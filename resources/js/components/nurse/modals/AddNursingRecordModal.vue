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
defineProps(['modelValue']);
defineEmits(['update:modelValue']);
</script>

<style scoped>
.modal-overlay { display: flex; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 500; align-items: center; justify-content: center; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-x { position: absolute; top: 12px; right: 12px; background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
</style>

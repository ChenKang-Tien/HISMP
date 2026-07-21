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

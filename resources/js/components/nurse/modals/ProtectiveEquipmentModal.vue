<template>
<div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box">
            <div class="modal-hdr">
                <span>🛡️ 新增臨床保護設備核定</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>勾選今日使用設備（多選）：</label>
                    <div
                        style="
                            display: flex;
                            flex-direction: column;
                            gap: 6px;
                            margin-top: 6px;
                        "
                    >
                        <label
                            v-for="item in options"
                            :key="item"
                            style="
                                display: flex;
                                align-items: center;
                                gap: 6px;
                                cursor: pointer;
                            "
                        >
                            <input
                                type="checkbox"
                                :value="item"
                                v-model="form.equipments"
                            />
                            {{ item }}
                        </label>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 12px">
                    <label>保護約束因由：</label>
                    <select v-model="form.reason" class="modal-input">
                        <option value="預防拔針 / 躁動風險">
                            預防拔針 / 躁動風險
                        </option>
                        <option value="透析中意識不清合作度佳">
                            透析中意識清楚但配合度欠佳
                        </option>
                        <option value="高跌倒風險交接">高跌倒風險交接</option>
                    </select>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button
                    class="btn btn-teal"
                    @click="submit"
                    :disabled="form.equipments.length === 0"
                >
                    確認套用並產生病歷
                </button>
            </div>
        </div>
    </div></input></div></div></div></div></div>
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

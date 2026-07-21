<template>
<div v-if="modelValue" class="modal-overlay" @click.self="close">
        <div class="modal-box">
            <div class="modal-hdr">
                <span>🩻 核發離院假單作業</span>
                <button class="close-x" @click="close">×</button>
            </div>
            <div class="modal-body">
                <p
                    style="
                        margin-bottom: 10px;
                        font-weight: bold;
                        color: #1e293b;
                    "
                >
                    病患：{{ patient?.name }} ({{ patient?.bed }}床 /
                    {{ patient?.mr }})
                </p>

                <div class="form-group">
                    <label>請選擇離院流轉狀態：</label>
                    <div style="display: flex; gap: 15px; margin-top: 5px">
                        <label
                            style="
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                gap: 4px;
                            "
                        >
                            <input
                                type="radio"
                                v-model="form.status"
                                value="LEAVE"
                            />
                            📋 病患請假
                        </label>
                        <label
                            style="
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                gap: 4px;
                            "
                        >
                            <input
                                type="radio"
                                v-model="form.status"
                                value="HOSPITALIZED"
                            />
                            🏥 轉院住院
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 12px">
                    <label>臨床主訴／交接備註：</label>
                    <textarea
                        v-model="form.note"
                        placeholder="請輸入病患請假主訴、住院醫院與交接因由..."
                        class="modal-input"
                        rows="3"
                    ></textarea>
                </div>
            </div>
            <div class="modal-ftr">
                <button class="btn btn-slate" @click="close">取消</button>
                <button
                    class="btn btn-teal"
                    @click="submit"
                    :disabled="!form.status"
                >
                    具名核準假單
                </button>
            </div>
        </div>
    </div></input></input></div></div></div></div></div>
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

<template>
<div v-if="modelValue" class="modal-mask" @click.self="closeModal">
        <div class="modal-container border-teal-bd">
            <div class="modal-header text-teal bg-teal-lt">
                <span class="font-700"
                    ><i class="ti ti-scale"></i> 1.
                    透析前/後體重數據精準校正</span
                >
                <button class="close-x text-teal" @click="closeModal">×</button>
            </div>

            <div class="modal-body">
                <div class="meta-desc">
                    ⚠️
                    護理提示：本輸入框內建平板<b>智慧小數點</b>快輸模式。例如欲輸入
                    <span class="text-teal font-700">65.2</span>
                    kg，請直接連續按下數字鍵
                    <span class="bg-slate-ul px-2 font-700">6</span
                    ><span class="bg-slate-ul px-2 font-700">5</span
                    ><span class="bg-slate-ul px-2 font-700">2</span>
                    即可，不需尋找點擊小數點。
                </div>

                <div class="v24-form-stack">
                    <div class="v24-form-item">
                        <label class="font-700 text-slate"
                            >今日透前原始體重 (未扣 kg)</label
                        >
                        <input
                            type="text"
                            inputmode="numeric"
                            v-model="displayPre"
                            @input="handlePreInput"
                            placeholder="請輸入透前原始體重數字"
                            class="v24-input focus-teal"
                        />
                    </div>

                    <div class="v24-form-item" style="margin-top: 12px">
                        <label class="font-700 text-slate"
                            >今日透後原始體重 (未扣 kg)</label
                        >
                        <input
                            type="text"
                            inputmode="numeric"
                            v-model="displayPost"
                            @input="handlePostInput"
                            placeholder="尚未量測可留空"
                            class="v24-input focus-teal"
                        />
                    </div>
                </div>

                <div
                    class="formula-box bg-teal-lt border-teal-bd"
                    style="margin-top: 15px"
                >
                    <div class="fb-row">
                        <span>目前套用總扣重明細合計：</span>
                        <span class="font-700 text-teal"
                            >-{{ store.deductionTotal.toFixed(1) }} kg</span
                        >
                    </div>
                    <div
                        class="fb-row"
                        style="
                            margin-top: 5px;
                            border-top: 1px dashed #99f6e4;
                            padding-top: 5px;
                        "
                    >
                        <span>計算後今日透前淨體重：</span>
                        <span
                            class="font-700 text-teal-dk"
                            style="font-size: 13px"
                        >
                            {{
                                (store.preRawWeight
                                    ? store.preRawWeight - store.deductionTotal
                                    : 0
                                ).toFixed(2)
                            }}
                            kg
                        </span>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-teal-lt">
                <button class="btn-v24-cancel" @click="closeModal">取消</button>
                <button class="btn-v24-confirm bg-teal-dk" @click="handleSave">
                    💾 具名校正寫入大腦
                </button>
            </div>
        </div>
    </div></input></div></input></div></div></div></div></div>
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

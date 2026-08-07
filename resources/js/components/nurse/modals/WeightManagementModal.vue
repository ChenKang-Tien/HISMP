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
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(["update:modelValue"]);
const store = useDialysisStore();

const displayPre = ref("");
const displayPost = ref("");

// 監聽開啟，精確帶入 store 現有浮點數並轉為字串顯示
watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            displayPre.value = store.preRawWeight
                ? store.preRawWeight.toFixed(1)
                : "";
            displayPost.value = store.postRawWeight
                ? store.postRawWeight.toFixed(1)
                : "";
        }
    },
);

// 🌟 智慧小數點輸入核心算法（打 799 自動轉 79.9）
const formatSmartDecimal = (val) => {
    let clean = val.replace(/\D/g, ""); // 只保留數字
    if (!clean) return "";
    if (clean.length === 1) return (parseInt(clean) / 10).toFixed(1);
    return (parseInt(clean) / 10).toFixed(1);
};

const handlePreInput = (e) => {
    displayPre.value = formatSmartDecimal(e.target.value);
};

const handlePostInput = (e) => {
    displayPost.value = formatSmartDecimal(e.target.value);
};

const closeModal = () => {
    emit("update:modelValue", false);
};

const handleSave = async () => {
    const pre = displayPre.value ? parseFloat(displayPre.value) : null;
    const post = displayPost.value ? parseFloat(displayPost.value) : null;
    
    // 透過 Store 進行 API 操作並觸發日誌備份
    const success = await store.updatePatientWeights(store.currentPatient.mr, {
        pre,
        post,
        note: '體重數據校正'
    });

    if (success) {
        store.preRawWeight = pre;
        store.postRawWeight = post;
        // 連鎖更新已扣體重大盤數據
        if (store.preRawWeight) {
            store.preAdjWeight = parseFloat(
                (store.preRawWeight - store.deductionTotal).toFixed(2),
            );
        }
        closeModal();
    }
};
</script>

<style scoped>
/* v24 醫療專用高階遮罩排版 */
.modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-container {
    width: 340px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
    border: 1.5px solid;
    overflow: hidden;
}
.modal-header {
    padding: 10px 14px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}
.close-x {
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    font-weight: bold;
}
.modal-body {
    padding: 14px;
    font-size: 11px;
    line-height: 1.4;
}
.meta-desc {
    color: #64748b;
    background: #f8fafc;
    padding: 8px;
    border-radius: 5px;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
}
.v24-input {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 5px 8px;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-top: 4px;
    box-sizing: border-box;
    text-align: center;
}
.focus-teal:focus {
    outline: none;
    border-color: #99f6e4;
    box-shadow: 0 0 0 3px rgba(153, 246, 228, 0.4);
}
.formula-box {
    padding: 9px;
    border-radius: 6px;
    border: 1px solid;
}
.fb-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-footer {
    padding: 8px 14px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 6px;
}
.btn-v24-cancel {
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 11px;
    cursor: pointer;
    color: #475569;
}
.btn-v24-confirm {
    border: none;
    border-radius: 4px;
    padding: 4px 12px;
    font-size: 11px;
    cursor: pointer;
    color: white;
    font-weight: 700;
}
.px-2 {
    padding-left: 4px;
    padding-right: 4px;
    margin-left: 2px;
    margin-right: 2px;
    border-radius: 3px;
    border: 1px solid #cbd5e1;
}
/* 配色對齊 */
.bg-teal-lt {
    background-color: #f0fdfa;
}
.border-teal-bd {
    border-color: #99f6e4;
}
.bg-slate-ul {
    background-color: #f1f5f9;
}
.text-teal {
    color: #0f766e;
}
.text-teal-dk {
    color: #134e4a;
}
.bg-teal-dk {
    background-color: #0f766e;
}
.text-slate {
    color: #475569;
}
.font-700 {
    font-weight: 700;
}
</style>

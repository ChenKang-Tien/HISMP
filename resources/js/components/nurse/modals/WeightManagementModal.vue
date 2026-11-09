<template>
    <div v-if="modelValue" class="modal-mask" @click.self="closeModal">
        <div class="modal-container border-teal-bd">
            <!-- 彈窗頂部 Header -->
            <div class="modal-header text-teal bg-teal-lt">
                <div class="header-left">
                    <span class="font-700 header-title">
                        <i class="ti ti-scale"></i> 透析體重與加減項目管理
                    </span>
                    <span v-if="store.currentPatient" class="pt-badge">
                        床號: {{ store.currentPatient.bed || "—" }} |
                        {{ store.currentPatient.name || "未選病患" }} ({{
                            store.currentPatient.mr || "—"
                        }})
                    </span>
                </div>
                <button
                    class="close-x text-teal"
                    @click="closeModal"
                    title="關閉"
                >
                    ×
                </button>
            </div>

            <!-- 彈窗內容 Modal Body -->
            <div class="modal-body">
                <!-- 護理提示 -->
                <div class="meta-desc">
                    <span class="meta-icon">💡</span>
                    <div>
                        <b>智慧小數點快輸</b
                        >：輸入體重時直接連續按下數字鍵（例如欲輸入
                        <span class="text-teal font-700">65.2</span> kg 輸入
                        <span class="kbd-hint">6</span
                        ><span class="kbd-hint">5</span
                        ><span class="kbd-hint">2</span>
                        即可）。加減項目支援正負小數（負數為扣除，正數為加重）。
                    </div>
                </div>

                <!-- 區塊一：今日過磅原始體重輸入 -->
                <div class="section-card">
                    <div class="sec-label text-slate">
                        <i class="ti ti-weight"></i> 今日過磅原始體重
                        (未扣除加減項 kg)
                    </div>
                    <div class="weights-grid">
                        <div class="weight-col">
                            <label class="input-sub-lbl text-teal font-700">
                                透前原始體重 (kg)
                            </label>
                            <input
                                type="text"
                                inputmode="numeric"
                                v-model="displayPre"
                                @input="handlePreInput"
                                placeholder="請輸入透前原始體重"
                                class="v24-input focus-teal text-teal font-700"
                            />
                        </div>
                        <div class="weight-col">
                            <div class="post-lbl-row">
                                <label
                                    class="input-sub-lbl text-slate font-700"
                                >
                                    透後原始體重 (kg)
                                </label>
                                <span class="post-note text-slate-lt"
                                    >尚未量測可留空</span
                                >
                            </div>
                            <input
                                type="text"
                                inputmode="numeric"
                                v-model="displayPost"
                                @input="handlePostInput"
                                placeholder="尚未量測可留空"
                                class="v24-input focus-teal text-slate font-700"
                            />
                        </div>
                    </div>
                </div>

                <!-- 區塊二：體重加減調整項目池 (扣重 / 加重品項) -->
                <div class="section-card" style="margin-top: 14px">
                    <div class="adjust-header-row">
                        <div class="sec-label text-slate">
                            <i class="ti ti-adjustments-horizontal"></i>
                            體重加減項目管理池
                        </div>
                        <!-- 透前 / 透後 分頁切換膠囊 -->
                        <div class="cat-pill-switch">
                            <button
                                type="button"
                                class="cat-tab-btn"
                                :class="{ active: currentCategory === 'pre' }"
                                @click="currentCategory = 'pre'"
                            >
                                透前調整 ({{
                                    (store.preDeductions || []).length
                                }})
                            </button>
                            <button
                                type="button"
                                class="cat-tab-btn"
                                :class="{ active: currentCategory === 'post' }"
                                @click="currentCategory = 'post'"
                            >
                                透後調整 ({{
                                    (store.postDeductions || []).length
                                }})
                            </button>
                        </div>
                    </div>

                    <!-- 膠囊池展示區 -->
                    <div class="pills-pool-box">
                        <div class="pool-meta-row">
                            <span class="pool-title text-slate font-700">
                                {{
                                    currentCategory === "pre"
                                        ? "透前現行套用項目"
                                        : "透後現行套用項目"
                                }}：
                            </span>
                            <div class="pool-actions">
                                <button
                                    v-if="
                                        currentCategory === 'pre' &&
                                        store.postDeductions?.length
                                    "
                                    type="button"
                                    class="btn-copy-pre"
                                    @click="confirmCopyPostItems"
                                    title="一鍵將透後所有加減項目帶入透前"
                                >
                                    <i class="ti ti-copy"></i> 帶入透後項目
                                </button>
                                <button
                                    v-if="
                                        currentCategory === 'post' &&
                                        store.preDeductions?.length
                                    "
                                    type="button"
                                    class="btn-copy-pre"
                                    @click="confirmCopyPreItems"
                                    title="一鍵將透前所有加減項目帶入透後"
                                >
                                    <i class="ti ti-copy"></i> 帶入透前項目
                                </button>
                                <span class="cat-total-badge">
                                    小計:
                                    <b
                                        >{{
                                            (currentCategoryTotal >= 0
                                                ? "+"
                                                : "") +
                                            currentCategoryTotal.toFixed(1)
                                        }}
                                        kg</b
                                    >
                                </span>
                            </div>
                        </div>

                        <!-- 膠囊列表 -->
                        <div
                            v-if="!activeDeductions.length"
                            class="empty-pills-text"
                        >
                            今日尚未設定{{
                                currentCategory === "pre" ? "透前" : "透後"
                            }}加減項目（可於下方選取新增）
                        </div>
                        <div v-else class="pill-container">
                            <div
                                v-for="d in activeDeductions"
                                :key="d.id"
                                class="applied-pill"
                            >
                                <span class="pill-name">{{ d.name }}</span>
                                <span
                                    class="pill-weight"
                                    :class="
                                        Number(d.weight) >= 0
                                            ? 'text-teal'
                                            : 'text-amber'
                                    "
                                >
                                    {{ Number(d.weight) >= 0 ? "+" : ""
                                    }}{{ Number(d.weight).toFixed(1) }} kg
                                </span>
                                <button
                                    type="button"
                                    class="pill-remove-btn"
                                    @click.stop.prevent="removeItem(d.id)"
                                    title="移除項目"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 快速新增加減項目列 -->
                    <div class="quick-add-card">
                        <div class="add-row">
                            <div class="form-field select-field">
                                <label class="field-label">選擇項目</label>
                                <select
                                    v-model="selectedItem"
                                    class="field-select"
                                >
                                    <option value="" disabled>
                                        請選擇預設調整品項
                                    </option>
                                    <option
                                        v-for="it in weightAdjustItems"
                                        :key="it.id"
                                        :value="it"
                                    >
                                        {{ it.item }} (預設:
                                        {{
                                            Number(it.default_weight) >= 0
                                                ? "+"
                                                : ""
                                        }}{{
                                            Number(it.default_weight).toFixed(
                                                1,
                                            )
                                        }}kg)
                                    </option>
                                </select>
                            </div>
                            <div class="form-field weight-field">
                                <label class="field-label">調整重量 (kg)</label>
                                <input
                                    type="text"
                                    inputmode="decimal"
                                    v-model="newItemWeightRaw"
                                    @input="handleWeightInput"
                                    placeholder=""
                                    class="field-input text-center font-700"
                                />
                            </div>
                            <button
                                type="button"
                                class="btn-add-item"
                                :disabled="!selectedItem || !newItemWeightRaw"
                                @click="addItem"
                            >
                                <i class="ti ti-plus"></i> 新增至{{
                                    currentCategory === "pre" ? "透前" : "透後"
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 區塊三：即時試算總覽 (Formula & Net Calculation Box) -->
                <div
                    class="formula-box bg-teal-lt border-teal-bd"
                    style="margin-top: 14px"
                >
                    <div class="formula-hdr">
                        <span class="font-700 text-teal-dk"
                            ><i class="ti ti-calculator"></i>
                            即時體重試算與淨重預覽</span
                        >
                        <span class="dry-weight-tag"
                            >乾體重基準:
                            {{ Number(store.dryWeight || 0).toFixed(1) }}
                            kg</span
                        >
                    </div>

                    <div class="formula-grid">
                        <div class="fg-item">
                            <div class="fg-lbl">透前淨體重 (真實值)</div>
                            <div class="fg-val text-teal-dk">
                                <span v-if="calculatedPreAdj !== null">
                                    {{ calculatedPreAdj.toFixed(2) }}
                                    <span class="unit">kg</span>
                                </span>
                                <span v-else class="text-slate-lt">—</span>
                            </div>
                            <div class="fg-sub text-slate">
                                原始
                                {{
                                    preRawVal > 0 ? preRawVal.toFixed(1) : "0.0"
                                }}
                                + 調整
                                {{
                                    (preDeductionTotal >= 0 ? "+" : "") +
                                    preDeductionTotal.toFixed(1)
                                }}
                            </div>
                        </div>

                        <div class="fg-divider"></div>

                        <div class="fg-item">
                            <div class="fg-lbl">透後淨體重 (真實值)</div>
                            <div class="fg-val text-teal-dk">
                                <span v-if="calculatedPostAdj !== null">
                                    {{ calculatedPostAdj.toFixed(2) }}
                                    <span class="unit">kg</span>
                                </span>
                                <span v-else class="text-slate-lt"
                                    >尚未量測</span
                                >
                            </div>
                            <div class="fg-sub text-slate">
                                原始
                                {{
                                    postRawVal !== null
                                        ? postRawVal.toFixed(1)
                                        : "—"
                                }}
                                + 調整
                                {{
                                    (postDeductionTotal >= 0 ? "+" : "") +
                                    postDeductionTotal.toFixed(1)
                                }}
                            </div>
                        </div>

                        <div class="fg-divider"></div>

                        <div class="fg-item highlight-uf">
                            <div class="fg-lbl text-teal-dk">
                                {{
                                    calculatedActualUF !== null
                                        ? "今日實際脫水 (UF)"
                                        : "預估應調水 (目標UF)"
                                }}
                            </div>
                            <div
                                class="fg-val"
                                :class="
                                    calculatedActualUF !== null
                                        ? 'text-teal font-700'
                                        : 'text-amber font-700'
                                "
                            >
                                <span v-if="calculatedActualUF !== null">
                                    {{ calculatedActualUF.toFixed(2) }}
                                    <span class="unit">kg</span>
                                </span>
                                <span v-else-if="calculatedTargetUF !== null">
                                    {{ calculatedTargetUF.toFixed(2) }}
                                    <span class="unit">kg</span>
                                </span>
                                <span v-else class="text-slate-lt">—</span>
                            </div>
                            <div class="fg-sub text-slate">
                                <span v-if="calculatedActualUF !== null"
                                    >透前淨重 - 透後淨重</span
                                >
                                <span v-else>透前淨重 - 乾體重</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 彈窗底部操作列 Modal Footer -->
            <div class="modal-footer bg-teal-lt">
                <button
                    type="button"
                    class="btn-v24-cancel"
                    @click="closeModal"
                >
                    取消
                </button>
                <button
                    type="button"
                    class="btn-v24-confirm bg-teal-dk"
                    :disabled="isSaving"
                    @click="handleSave"
                >
                    <span v-if="isSaving"
                        ><i class="ti ti-loader"></i> 儲存中...</span
                    >
                    <span v-else
                        ><i class="ti ti-check"></i> 儲存體重與項目</span
                    >
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    initialCategory: {
        type: String,
        default: "pre",
    },
});

const emit = defineEmits(["update:modelValue"]);
const store = useDialysisStore();
const api = store.api;

const displayPre = ref("");
const displayPost = ref("");
const currentCategory = ref("pre");
const selectedItem = ref("");
const weightAdjustItems = ref([]);
const newItemWeightRaw = ref("");
const isSaving = ref(false);

// 載入預設加減項目池清單
const reloadItems = async () => {
    try {
        const res = await api.get("/weight-adjust-items");
        weightAdjustItems.value = res.data || [];
    } catch (e) {
        console.error("無法載入加減品項列表:", e);
    }
};

onMounted(() => {
    reloadItems();
});

// 監聽彈窗開啟，同步 store 既有數值
watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            displayPre.value = store.preRawWeight
                ? Number(store.preRawWeight).toFixed(1)
                : "";
            displayPost.value = store.postRawWeight
                ? Number(store.postRawWeight).toFixed(1)
                : "";
            currentCategory.value =
                props.initialCategory === "post" ? "post" : "pre";
            selectedItem.value = "";
            newItemWeightRaw.value = "";
            reloadItems();
        }
    },
);

watch(
    () => props.initialCategory,
    (cat) => {
        if (cat) {
            currentCategory.value = cat === "post" ? "post" : "pre";
        }
    },
);

// 當選取調整項目時，自動帶入該項目的預設重量
watch(selectedItem, (newVal) => {
    if (newVal) {
        newItemWeightRaw.value = String(newVal.default_weight ?? "");
    }
});

// 🌟 智慧小數點輸入算法（打 652 自動轉 65.2）
const formatSmartDecimal = (val) => {
    let clean = val.replace(/\D/g, "");
    if (!clean) return "";
    return (parseInt(clean, 10) / 10).toFixed(1);
};

const handlePreInput = (e) => {
    displayPre.value = formatSmartDecimal(e.target.value);
};

const handlePostInput = (e) => {
    displayPost.value = formatSmartDecimal(e.target.value);
};

const handleWeightInput = (e) => {
    // 允許負號與數字、小數點
    newItemWeightRaw.value = String(e.target.value).replace(/[^0-9.-]/g, "");
};

// 當前類別調整項目清單
const activeDeductions = computed(() => {
    return currentCategory.value === "pre"
        ? store.preDeductions || []
        : store.postDeductions || [];
});

// 當前類別調整總和
const currentCategoryTotal = computed(() => {
    return (activeDeductions.value || []).reduce(
        (sum, d) => sum + (Number(d.weight) || 0),
        0,
    );
});

// 透前 / 透後加減項目總和
const preDeductionTotal = computed(() => {
    return (store.preDeductions || []).reduce(
        (sum, d) => sum + (Number(d.weight) || 0),
        0,
    );
});

const postDeductionTotal = computed(() => {
    return (store.postDeductions || []).reduce(
        (sum, d) => sum + (Number(d.weight) || 0),
        0,
    );
});

// 即時數值解析
const preRawVal = computed(() => {
    const val = parseFloat(displayPre.value);
    return isNaN(val) ? 0 : val;
});

const postRawVal = computed(() => {
    const val = parseFloat(displayPost.value);
    return isNaN(val) ? null : val;
});

// 計算後淨體重
const calculatedPreAdj = computed(() => {
    if (!displayPre.value || preRawVal.value === 0) return null;
    return parseFloat((preRawVal.value + preDeductionTotal.value).toFixed(2));
});

const calculatedPostAdj = computed(() => {
    if (postRawVal.value === null) return null;
    return parseFloat((postRawVal.value + postDeductionTotal.value).toFixed(2));
});

// 脫水量試算
const calculatedActualUF = computed(() => {
    if (calculatedPreAdj.value !== null && calculatedPostAdj.value !== null) {
        return parseFloat(
            (calculatedPreAdj.value - calculatedPostAdj.value).toFixed(2),
        );
    }
    return null;
});

const calculatedTargetUF = computed(() => {
    if (calculatedPreAdj.value !== null) {
        const dry = parseFloat(store.dryWeight || 0);
        return parseFloat(Math.max(0, calculatedPreAdj.value - dry).toFixed(2));
    }
    return null;
});

// 快速新增加減項目
const addItem = async () => {
    if (!selectedItem.value || !newItemWeightRaw.value) return;

    const cat = currentCategory.value;
    const item = {
        id: Date.now() + Math.random(),
        item_id: selectedItem.value.id,
        name: selectedItem.value.item,
        weight: parseFloat(newItemWeightRaw.value),
        category: cat,
    };

    if (cat === "pre") {
        store.preDeductions.push(item);
    } else {
        store.postDeductions.push(item);
    }

    const checkId = store.currentPatient?.id;
    if (checkId) {
        await store.syncWeightAdjustments(checkId);
    }
    store.calculateWeights();

    // 重設輸入框
    newItemWeightRaw.value = "";
    selectedItem.value = "";
};

// 移除加減項目
const removeItem = async (id) => {
    const cat = currentCategory.value;
    if (cat === "pre") {
        store.preDeductions = (store.preDeductions || []).filter(
            (d) => d.id !== id,
        );
    } else {
        store.postDeductions = (store.postDeductions || []).filter(
            (d) => d.id !== id,
        );
    }

    const checkId = store.currentPatient?.id;
    if (checkId) {
        await store.syncWeightAdjustments(checkId);
    }
    store.calculateWeights();
};

// 複製透前加減項目至透後
const confirmCopyPreItems = async () => {
    if (!store.preDeductions || store.preDeductions.length === 0) {
        alert("目前透前尚未設定任何加減項目可供複製！");
        return;
    }
    if (
        !confirm(
            "確定要將透前調整項目複製到透後嗎？此操作將覆蓋現有透後項目並即時儲存。",
        )
    ) {
        return;
    }

    store.postDeductions = store.preDeductions.map((d) => ({
        ...d,
        id: Date.now() + Math.random(),
        category: "post",
    }));

    const checkId = store.currentPatient?.id;
    if (checkId) {
        await store.syncWeightAdjustments(checkId);
    }
    store.calculateWeights();
};

// 複製透後加減項目至透前
const confirmCopyPostItems = async () => {
    if (!store.postDeductions || store.postDeductions.length === 0) {
        alert("目前透後尚未設定任何加減項目可供複製！");
        return;
    }
    if (
        !confirm(
            "確定要將透後調整項目複製到透前嗎？此操作將覆蓋現有透前項目並即時儲存。",
        )
    ) {
        return;
    }

    store.preDeductions = store.postDeductions.map((d) => ({
        ...d,
        id: Date.now() + Math.random(),
        category: "pre",
    }));

    const checkId = store.currentPatient?.id;
    if (checkId) {
        await store.syncWeightAdjustments(checkId);
    }
    store.calculateWeights();
};

const closeModal = () => {
    emit("update:modelValue", false);
};

// 儲存體重與加減項目
const handleSave = async () => {
    const checkId = store.currentPatient?.id;
    if (!checkId) {
        alert("請先選取有效之透析病患！");
        return;
    }

    isSaving.value = true;
    try {
        const pre = displayPre.value ? parseFloat(displayPre.value) : null;
        const post = displayPost.value ? parseFloat(displayPost.value) : null;

        // 1. 同步體重至後端
        const weightSuccess = await store.updatePatientWeights(checkId, {
            pre,
            post,
            note: "透析前/後體重與加減項目校正",
        });

        // 2. 同步加減項目至後端
        const adjustSuccess = await store.syncWeightAdjustments(checkId);

        if (weightSuccess && adjustSuccess) {
            store.preRawWeight = pre;
            store.postRawWeight = post;
            store.calculateWeights();

            // 更新 store.currentPatient.weight_info 以供外部面板即時響應
            if (store.currentPatient) {
                store.currentPatient.weight_info = {
                    pre_raw_weight: pre,
                    post_raw_weight: post,
                    pre: pre,
                    dry: store.dryWeight,
                    pre_deduction: store.preDeductionTotal,
                    post_deduction: store.postDeductionTotal,
                    deduction: store.preDeductionTotal,
                    pre_adj: store.preAdjWeight,
                    post_adj: store.postAdjWeight,
                    pre_items: store.preDeductions,
                    post_items: store.postDeductions,
                };
            }
            closeModal();
        } else {
            alert("儲存過程發生錯誤，請檢查網路連線或重試。");
        }
    } catch (e) {
        console.error("儲存失敗:", e);
        alert("儲存失敗，請重試！");
    } finally {
        isSaving.value = false;
    }
};
</script>

<style scoped>
/* 遮罩排版 */
.modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-container {
    width: 94%;
    max-width: 580px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.25);
    border: 1.5px solid #99f6e4;
    overflow: hidden;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

/* Header */
.modal-header {
    padding: 11px 16px;
    border-bottom: 1px solid #ccfbf1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.header-title {
    font-size: 14.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.pt-badge {
    background: #ccfbf1;
    color: #115e59;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 600;
}

.close-x {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    font-weight: bold;
    padding: 0 4px;
    line-height: 1;
}

/* Modal Body */
.modal-body {
    padding: 14px 16px;
    overflow-y: auto;
    font-size: 13px;
    flex: 1;
}

.meta-desc {
    color: #475569;
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 6px;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    font-size: 12px;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    line-height: 1.45;
}

.meta-icon {
    font-size: 14px;
}

.kbd-hint {
    background: #e2e8f0;
    padding: 1px 4px;
    border-radius: 3px;
    font-weight: 700;
    margin: 0 1px;
}

.section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
}

.sec-label {
    font-size: 12.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 8px;
}

/* 體重輸入欄位 */
.weights-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.weight-col {
    display: flex;
    flex-direction: column;
}

.input-sub-lbl {
    font-size: 12px;
    margin-bottom: 4px;
}

.post-lbl-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.post-note {
    font-size: 10.5px;
}

.v24-input {
    width: 100%;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 14px;
    box-sizing: border-box;
    text-align: center;
}

.focus-teal:focus {
    outline: none;
    border-color: #0f766e;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
}

/* 調整項目區 */
.adjust-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 6px;
}

.cat-pill-switch {
    display: flex;
    background: #f1f5f9;
    border-radius: 6px;
    padding: 2px;
    border: 1px solid #e2e8f0;
}

.cat-tab-btn {
    border: none;
    background: none;
    padding: 3px 10px;
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s;
}

.cat-tab-btn.active {
    background: #0f766e;
    color: white;
    font-weight: 700;
}

.pills-pool-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
}

.pool-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.pool-title {
    font-size: 11.5px;
}

.pool-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-copy-pre {
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    color: #0f766e;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 3px;
}

.btn-copy-pre:hover {
    background: #ccfbf1;
}

.cat-total-badge {
    font-size: 11px;
    color: #475569;
    background: #e2e8f0;
    padding: 2px 6px;
    border-radius: 4px;
}

.empty-pills-text {
    font-size: 11.5px;
    color: #94a3b8;
    text-align: center;
    padding: 8px 0;
}

.pill-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    max-height: 85px;
    overflow-y: auto;
    padding: 2px 0;
}

.applied-pill {
    background: white;
    border: 1.5px solid #99f6e4;
    border-radius: 14px;
    padding: 3px 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.pill-name {
    color: #0f766e;
    font-weight: 600;
}

.pill-weight {
    font-weight: 700;
}

.pill-remove-btn {
    border: none;
    background: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 11px;
    font-weight: bold;
    padding: 0 2px;
    line-height: 1;
}

.pill-remove-btn:hover {
    color: #b91c1c;
}

/* 快速新增列 */
.quick-add-card {
    margin-top: 8px;
    border-top: 1px dashed #e2e8f0;
    padding-top: 8px;
}

.add-row {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    width: 100%;
    box-sizing: border-box;
}

.form-field {
    display: flex;
    flex-direction: column;
}

.select-field {
    flex: 1 1 auto;
    min-width: 0;
}

.weight-field {
    flex: 0 0 105px;
    width: 105px;
    box-sizing: border-box;
}

.field-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 2px;
}

.field-select {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    padding: 5px 8px;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    font-size: 12.5px;
    height: 32px;
    box-sizing: border-box;
    text-overflow: ellipsis;
}

.field-input {
    width: 100%;
    padding: 5px 8px;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    height: 32px;
    box-sizing: border-box;
}

.btn-add-item {
    flex-shrink: 0;
    background: #0f766e;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 0 12px;
    font-size: 12px;
    font-weight: 700;
    height: 32px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    white-space: nowrap;
    box-sizing: border-box;
}

.btn-add-item:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}

/* 試算方塊 */
.formula-box {
    border-radius: 8px;
    padding: 10px 14px;
    border: 1.5px solid #99f6e4;
}

.formula-hdr {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    margin-bottom: 8px;
    border-bottom: 1px dashed #99f6e4;
    padding-bottom: 4px;
}

.dry-weight-tag {
    font-size: 11px;
    color: #0f766e;
    background: #ccfbf1;
    padding: 1px 6px;
    border-radius: 4px;
    font-weight: 600;
}

.formula-grid {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.fg-item {
    flex: 1;
    text-align: center;
}

.fg-lbl {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 2px;
}

.fg-val {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
}

.fg-val .unit {
    font-size: 11px;
    font-weight: normal;
}

.fg-sub {
    font-size: 10px;
    margin-top: 2px;
}

.fg-divider {
    width: 1px;
    height: 34px;
    background: #99f6e4;
    margin: 0 8px;
}

.highlight-uf {
    background: rgba(255, 255, 255, 0.6);
    padding: 4px 6px;
    border-radius: 6px;
}

/* Footer */
.modal-footer {
    padding: 10px 16px;
    border-top: 1px solid #ccfbf1;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.btn-v24-cancel {
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 12.5px;
    cursor: pointer;
    color: #475569;
    font-weight: 600;
}

.btn-v24-confirm {
    border: none;
    border-radius: 6px;
    padding: 6px 16px;
    font-size: 12.5px;
    cursor: pointer;
    color: white;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
}

.btn-v24-confirm:disabled {
    background: #94a3b8;
    cursor: not-allowed;
}

/* 配色通用 class */
.bg-teal-lt {
    background-color: #f0fdfa;
}
.border-teal-bd {
    border-color: #99f6e4;
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
.text-amber {
    color: #b45309;
}
.text-amber-dk {
    color: #92400e;
}
.text-slate {
    color: #475569;
}
.text-slate-lt {
    color: #94a3b8;
}
.font-700 {
    font-weight: 700;
}
.text-center {
    text-align: center;
}
</style>

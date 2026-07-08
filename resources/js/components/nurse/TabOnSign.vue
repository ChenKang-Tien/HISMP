<template>
    <div class="onsign-tab-wrapper">
        <!-- 🟢 前置整備確認橫條 (100% 對齊原稿 Class 與點擊行為) -->
        <div class="confirm-bar" @click="showPrepModal = true">
            <span>☑</span>
            <span style="font-size: 12px; font-weight: 700">
                前置整備：FX80 Classix ｜ Heparin 2000u ｜ Epogin 4000u —
                已確認就位
            </span>
            <span
                style="
                    margin-left: auto;
                    font-size: 10px;
                    color: #0f766e;
                    background: #f0fdfa;
                    border: 1px solid #99f6e4;
                    border-radius: 4px;
                    padding: 1px 7px;
                "
            >
                查看明細 ▸
            </span>
        </div>

        <!-- 生命徵象標題與觸發按鈕 -->
        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 4px;
            "
        >
            <div class="sec-title">
                <i class="ti ti-activity"></i>On-Sign 生理數值
            </div>
            <button
                class="add-drug-btn"
                style="width: auto; margin-bottom: 0; padding: 3px 8px"
                @click="openVsignModal"
            >
                ✏️ 點入生理數值
            </button>
        </div>

        <!-- 五大生命徵象大排卡 (RWD 自動分配 5 欄) -->
        <div class="vsign-grid">
            <div class="vsign-box" @click="openVsignModal">
                <div class="vsign-l">
                    BP<br /><span style="font-size: 8px; color: #94a3b8"
                        >mmHg</span
                    >
                </div>
                <div class="vsign-v">{{ store.vsignData.bp }}</div>
            </div>
            <div class="vsign-box" @click="openVsignModal">
                <div class="vsign-l">
                    Pulse<br /><span style="font-size: 8px; color: #94a3b8"
                        >次/min</span
                    >
                </div>
                <div class="vsign-v">{{ store.vsignData.pr }}</div>
            </div>
            <div class="vsign-box" @click="openVsignModal">
                <div class="vsign-l">
                    Resp.<br /><span style="font-size: 8px; color: #94a3b8"
                        >次/min</span
                    >
                </div>
                <div class="vsign-v">{{ store.vsignData.rr }}</div>
            </div>
            <div class="vsign-box" @click="openVsignModal">
                <div class="vsign-l">
                    Temp.<br /><span style="font-size: 8px; color: #94a3b8"
                        >°C</span
                    >
                </div>
                <div class="vsign-v">{{ store.vsignData.temp }}</div>
            </div>
            <!-- 智慧防漏鎖：有血糖醫囑時外框與數值高亮連動 -->
            <div
                v-if="store.currentPatient.hasFSOrder"
                class="vsign-box"
                :style="fsBoxStyle"
                @click="openVsignModal"
            >
                <div class="vsign-l">
                    F/S<br /><span style="font-size: 8px; color: #94a3b8"
                        >mg/dL</span
                    >
                </div>
                <div
                    class="vsign-v"
                    :class="{ 'text-warn': store.vsignFilled }"
                >
                    {{ store.vsignData.fs }}
                </div>
            </div>
        </div>

        <!-- 三大評估主開盤 (完全還原原稿橫排大方塊與點擊叫出彈窗機制) -->
        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 6px;
                margin-bottom: 4px;
            "
        >
            <div class="sec-title" style="margin-bottom: 0">
                <i class="ti ti-stethoscope"></i>三大評估
            </div>
            <span
                style="cursor: pointer; color: #0f766e; font-size: 16px"
                @click="openAssessModal(0)"
                >✏️</span
            >
        </div>
        <div class="assess-grid">
            <div
                class="assess-box"
                @click="openAssessModal(0)"
                style="cursor: pointer"
            >
                <div>
                    <div class="assess-lbl">血管通路</div>
                    <div class="assess-val" id="assess-val-vascular">
                        {{ store.assessState.vascular }}
                    </div>
                </div>
            </div>
            <div
                class="assess-box"
                @click="openAssessModal(1)"
                style="cursor: pointer"
            >
                <div>
                    <div class="assess-lbl">意識狀態</div>
                    <div class="assess-val" id="assess-val-conscious">
                        {{ store.assessState.conscious }}
                    </div>
                </div>
            </div>
            <div
                class="assess-box"
                @click="openAssessModal(2)"
                style="cursor: pointer"
            >
                <div>
                    <div class="assess-lbl">皮膚完整性</div>
                    <div class="assess-val" id="assess-val-skin">
                        {{ store.assessState.skin }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 透前給藥卡片列 -->
        <div class="drug-section-hdr">
            <div class="sec-title" style="margin-bottom: 0">
                <i class="ti ti-pill"></i>透前給藥
            </div>
            <button class="add-drug-btn" @click="addPreDrugRow">
                ➕ 新增透前給藥記錄
            </button>
        </div>
        <div id="pre-drug-list" style="margin-bottom: 5px">
            <div style="display: flex; gap: 5px; flex-wrap: wrap">
                <div
                    v-for="(drug, index) in preDrugs"
                    :key="index"
                    :class="['pre-drug-item', { deleted: drug.isDeleted }]"
                >
                    <span
                        :style="
                            drug.isDeleted
                                ? 'text-decoration: line-through; color: #94a3b8;'
                                : ''
                        "
                    >
                        {{ drug.text }}
                    </span>
                    <button
                        v-if="!drug.isDeleted"
                        @click="deletePreDrug(index)"
                        class="delete-drug-x"
                    >
                        ✕
                    </button>
                    <span v-else class="action-note-tag"
                        >〈楚心瑜 11:50 刪除〉</span
                    >
                </div>
            </div>
        </div>

        <!-- 💉 透析用藥唯讀面板 (100% 還原原稿大色塊配比) -->
        <div
            style="
                font-size: 12px;
                font-weight: 700;
                color: #7c3aed;
                margin: 6px 0 4px;
                display: flex;
                align-items: center;
                gap: 4px;
            "
        >
            <i class="ti ti-needle"></i>💉 透析用藥
        </div>
        <div class="hep-readonly">
            <div class="hep-ro-title">
                <i class="ti ti-needle" style="margin-right: 3px"></i>Heparin /
                EPO 醫囑（醫師設定，護理端唯讀）
            </div>
            <div class="hep-drug-cols">
                <div class="hep-drug-col">
                    <div class="hep-drug-name">Heparin</div>
                    <div class="hep-drug-dose">Initial：2000 u</div>
                    <div class="hep-drug-dose">Maintain：300 u/hr</div>
                </div>
                <div class="hep-drug-col">
                    <div class="hep-drug-name">EPO（NESP）</div>
                    <div class="hep-drug-dose">N20（HCT 30~32.5）</div>
                    <div class="hep-drug-dose">W一·三 施打</div>
                </div>
            </div>
        </div>

        <!-- 核心安全雙簽章區 (未滿足血糖醫囑填寫時反灰) -->
        <div
            class="sign-row"
            :style="isSignDisabled ? 'opacity:0.5; pointer-events:none;' : ''"
        >
            <div
                :class="['sbtn', { signed: store.mainSigned }]"
                @click="executeMainOnSign"
            >
                {{
                    store.mainSigned
                        ? "✓ On-Sign 已簽·資料鎖定"
                        : "主責護理師 On-Sign"
                }}
            </div>
            <div
                :class="['sbtn', { signed: isDoubleSigned }]"
                @click="triggerDoubleSign"
            >
                {{
                    isDoubleSigned
                        ? "✓ Double Sign 已雙簽"
                        : "雙簽護理師 Double Sign"
                }}
            </div>
        </div>

        <!-- ═══════════════════════════════════ -->
        <!-- 🎛️ MODALS 彈窗完全還原（前置整備、三大評估統一、生命徵象） -->
        <!-- ═══════════════════════════════════ -->

        <!-- ① 前置整備彈窗 -->
        <div :class="['modal-overlay', { open: showPrepModal }]">
            <div class="modal">
                <div class="modal-hdr">
                    <i class="ti ti-checklist"></i
                    >前置整備確認（依醫囑自動產生）
                </div>
                <button class="modal-x" @click="showPrepModal = false">
                    ✕
                </button>
                <div
                    style="font-size: 11px; color: #94a3b8; margin-bottom: 8px"
                >
                    對照實物逐項確認就位
                </div>
                <div
                    style="
                        font-size: 10px;
                        font-weight: 700;
                        color: #475569;
                        margin-bottom: 5px;
                        text-transform: uppercase;
                        letter-spacing: 0.4px;
                    "
                >
                    醫器
                </div>
                <div
                    v-for="item in prepItems.device"
                    :key="item.id"
                    :class="['prep-item', { checked: item.checked }]"
                    @click="item.checked = !item.checked"
                >
                    <div class="prep-chk">{{ item.checked ? "✓" : "" }}</div>
                    {{ item.name }}
                </div>
                <div
                    style="
                        font-size: 10px;
                        font-weight: 700;
                        color: #475569;
                        margin: 8px 0 5px;
                        text-transform: uppercase;
                        letter-spacing: 0.4px;
                    "
                >
                    藥材
                </div>
                <div
                    v-for="item in prepItems.med"
                    :key="item.id"
                    :class="['prep-item', { checked: item.checked }]"
                    @click="item.checked = !item.checked"
                >
                    <div class="prep-chk">{{ item.checked ? "✓" : "" }}</div>
                    {{ item.name }}
                </div>
                <div
                    style="
                        background: #fffbeb;
                        border: 1px solid #fde68a;
                        border-radius: 6px;
                        padding: 6px 8px;
                        font-size: 11px;
                        color: #b45309;
                        margin-top: 8px;
                    "
                >
                    ⚠️ IRON 本月未到施打週，無需準備
                </div>
                <div class="mbtn-row">
                    <button class="mbtn sec" @click="showPrepModal = false">
                        關閉
                    </button>
                    <button class="mbtn pri" @click="showPrepModal = false">
                        ✅ 全部確認就位
                    </button>
                </div>
            </div>
        </div>

        <!-- ② 三大評估統一式大彈窗 (原稿 V20 DL-129 三頁籤配置) -->
        <div :class="['modal-overlay', { open: showAssessModal }]">
            <div class="modal" style="max-width: 500px">
                <div class="modal-hdr">
                    <i class="ti ti-stethoscope"></i>三大評估
                </div>
                <button class="modal-x" @click="showAssessModal = false">
                    ✕
                </button>

                <!-- 三分頁標籤按鈕 -->
                <div style="display: flex; gap: 4px; margin-bottom: 12px">
                    <button
                        :class="[
                            'assess-tab-btn',
                            { active: assessActiveTab === 0 },
                        ]"
                        @click="assessActiveTab = 0"
                    >
                        🌿 血管通路
                    </button>
                    <button
                        :class="[
                            'assess-tab-btn',
                            { active: assessActiveTab === 1 },
                        ]"
                        @click="assessActiveTab = 1"
                    >
                        🧠 意識狀態
                    </button>
                    <button
                        :class="[
                            'assess-tab-btn',
                            { active: assessActiveTab === 2 },
                        ]"
                        @click="assessActiveTab = 2"
                    >
                        🩹 皮膚完整性
                    </button>
                </div>

                <!-- 頁籤0：血管通路 -->
                <div v-show="assessActiveTab === 0">
                    <div class="form-label">種類</div>
                    <div class="assess-opt-row">
                        <span
                            v-for="k in ['AVF', 'AVG', 'Permcath', 'D/L']"
                            :key="k"
                            :class="[
                                'assess-opt',
                                { sel: assessForm.vascularKind === k },
                            ]"
                            @click="assessForm.vascularKind = k"
                        >
                            {{ k }}
                        </span>
                    </div>
                    <div
                        v-show="assessForm.vascularKind === 'Permcath'"
                        style="margin-bottom: 8px"
                    >
                        <div class="form-label">Permcath 長度</div>
                        <input
                            v-model="assessForm.permcathLen"
                            class="form-input"
                            type="text"
                            placeholder="如：155 → 15.5 cm"
                            style="margin-bottom: 0"
                        />
                    </div>
                    <div class="form-label">評估</div>
                    <div class="assess-opt-row">
                        <span
                            v-for="e in [
                                '正常',
                                '紅',
                                '腫',
                                '熱',
                                '痛',
                                '滲血',
                            ]"
                            :key="e"
                            :class="[
                                'assess-opt',
                                { sel: assessForm.vascularEval === e },
                            ]"
                            @click="assessForm.vascularEval = e"
                        >
                            {{ e }}
                        </span>
                    </div>
                </div>

                <!-- 頁籤1：意識狀態 -->
                <div v-show="assessActiveTab === 1">
                    <div class="form-label">意識狀態</div>
                    <div class="assess-opt-row">
                        <span
                            v-for="c in [
                                '清醒',
                                '嗜睡',
                                '木僵',
                                '半昏迷',
                                '昏迷',
                                '失智',
                            ]"
                            :key="c"
                            :class="[
                                'assess-opt',
                                { sel: assessForm.consciousState === c },
                            ]"
                            @click="assessForm.consciousState = c"
                        >
                            {{ c }}
                        </span>
                    </div>
                </div>

                <!-- 頁籤2：皮膚完整性 -->
                <div v-show="assessActiveTab === 2">
                    <div class="form-label">皮膚狀態</div>
                    <div class="assess-opt-row">
                        <span
                            v-for="s in ['完整', '有傷口（含水泡）']"
                            :key="s"
                            :class="[
                                'assess-opt',
                                { sel: assessForm.skinState === s },
                            ]"
                            @click="assessForm.skinState = s"
                        >
                            {{ s }}
                        </span>
                    </div>
                    <div class="form-row" style="margin-top: 8px">
                        <div class="form-col">
                            <div class="form-label">部位</div>
                            <input
                                v-model="assessForm.skinLocation"
                                class="form-input"
                                placeholder="部位..."
                                style="margin-bottom: 0"
                            />
                        </div>
                        <div class="form-col">
                            <div class="form-label">大小（cm）</div>
                            <input
                                v-model="assessForm.skinSize"
                                class="form-input"
                                placeholder="cm..."
                                style="margin-bottom: 0"
                            />
                        </div>
                    </div>
                </div>

                <div class="form-label" style="margin-top: 8px">備註</div>
                <textarea
                    v-model="assessForm.note"
                    class="form-input"
                    rows="2"
                    placeholder="自由文字備註..."
                    style="height: auto; resize: none"
                ></textarea>

                <div class="mbtn-row">
                    <button class="mbtn sec" @click="showAssessModal = false">
                        取消
                    </button>
                    <button class="mbtn pri" @click="saveUnifiedAssess">
                        ✅ 儲存評估
                    </button>
                </div>
            </div>
        </div>

        <!-- ③ 生命徵象手動填寫彈窗 (五大生理參數) -->
        <div :class="['modal-overlay', { open: showVsignModal }]">
            <div class="modal">
                <div class="modal-hdr">
                    <i class="ti ti-activity"></i>On-Sign 五大生命徵象輸入
                </div>
                <button class="modal-x" @click="showVsignModal = false">
                    ✕
                </button>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-label">血壓 BP（mmHg）</div>
                        <input
                            v-model="vsignForm.bp"
                            class="form-input"
                            placeholder="120/80"
                            style="margin-bottom: 0"
                        />
                    </div>
                    <div class="form-col">
                        <div class="form-label">脈搏 Pulse（次/min）</div>
                        <input
                            v-model="vsignForm.pr"
                            class="form-input"
                            placeholder="76"
                            style="margin-bottom: 0"
                        />
                    </div>
                </div>
                <div class="form-row" style="margin-top: 8px">
                    <div class="form-col">
                        <div class="form-label">呼吸 Resp.（次/min）</div>
                        <input
                            v-model="vsignForm.rr"
                            class="form-input"
                            placeholder="18"
                            style="margin-bottom: 0"
                        />
                    </div>
                    <div class="form-col">
                        <div class="form-label">體溫 Temp.（°C）</div>
                        <input
                            v-model="vsignForm.temp"
                            class="form-input"
                            placeholder="36.5"
                            style="margin-bottom: 0"
                        />
                    </div>
                </div>
                <div style="margin-top: 8px">
                    <div class="form-label">血糖 F/S（mg/dL）</div>
                    <input
                        v-model="vsignForm.fs"
                        class="form-input"
                        placeholder="100"
                        style="margin-bottom: 0"
                    />
                </div>
                <div class="mbtn-row">
                    <button class="mbtn sec" @click="showVsignModal = false">
                        取消
                    </button>
                    <button class="mbtn pri" @click="saveVsignForm">
                        ✅ 確認送出
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();

// 模態視窗與分頁指標
const showPrepModal = ref(false);
const showAssessModal = ref(false);
const showVsignModal = ref(false);
const assessActiveTab = ref(0);
const isDoubleSigned = ref(false);

// 靜態整備常組清單
const prepItems = ref({
    device: [
        { id: 1, name: "FX80 Classix（Dialyzer）× 1", checked: false },
        { id: 2, name: "透析管路組 × 1", checked: false },
        { id: 3, name: "穿刺針 × 2", checked: true },
    ],
    med: [
        { id: 4, name: "Heparin 2000u", checked: false },
        { id: 5, name: "Epogin 4000u（依醫囑施打）", checked: false },
    ],
});

// 透前動態處置藥物列 (加線留痕相容)
const preDrugs = ref([
    { text: "@07:10 Medorin #3顆 PO 楚心瑜", isDeleted: false },
    { text: "@08:05 Heparin Initial 2000U IV 楚心瑜", isDeleted: false },
]);

// 生命徵象內部表單暫存
const vsignForm = ref({ bp: "", pr: "", rr: "", temp: "", fs: "" });

// 三大評估暫存表單物件
const assessForm = ref({
    vascularKind: "AVF",
    permcathLen: "",
    vascularEval: "正常",
    consciousState: "清醒",
    skinState: "完整",
    skinLocation: "",
    skinSize: "",
    note: "",
});

// 智慧連動計算：血糖醫囑鎖定判定
const isSignDisabled = computed(() => {
    return (
        store.currentPatient.hasFSOrder &&
        (!store.vsignData.fs || store.vsignData.fs === "—")
    );
});

// 血糖方塊高亮連動樣式
const fsBoxStyle = computed(() => {
    if (!store.vsignFilled && store.currentPatient.hasFSOrder) {
        return { borderColor: "#fde68a", backgroundColor: "#fffbeb" };
    }
    return {};
});

// 開啟生命徵象設定
const openVsignModal = () => {
    vsignForm.value = {
        bp: store.vsignData.bp !== "—" ? store.vsignData.bp : "",
        pr: store.vsignData.pr !== "—" ? store.vsignData.pr : "",
        rr: store.vsignData.rr !== "—" ? store.vsignData.rr : "",
        temp:
            store.vsignData.temp !== "—"
                ? store.vsignData.temp.replace("°C", "")
                : "",
        fs: store.vsignData.fs !== "—" ? store.vsignData.fs : "",
    };
    showVsignModal.value = true;
};

// 儲存生命徵象 (格式化邏輯對齊原稿)
const saveVsignForm = () => {
    let bpVal = vsignForm.value.bp;
    const d = bpVal.replace(/\D/g, "");
    if (d.length === 4) bpVal = d.slice(0, 2) + "/" + d.slice(2);
    else if (d.length === 5 || d.length === 6)
        bpVal = d.slice(0, 3) + "/" + d.slice(3);

    let tempVal = vsignForm.value.temp;
    if (tempVal && !tempVal.includes("°C")) tempVal = tempVal + "°C";

    store.vsignData = {
        bp: bpVal || "—",
        pr: vsignForm.value.pr || "—",
        rr: vsignForm.value.rr || "—",
        temp: tempVal || "—",
        fs: vsignForm.value.fs || "—",
    };
    store.vsignFilled = true;
    showVsignModal.value = false;
};

// 開啟三大評估
const openAssessModal = (tabIndex) => {
    assessActiveTab.value = tabIndex;
    showAssessModal.value = true;
};

// 儲存三大評估 (完全對齊原稿文字生成模式)
const saveUnifiedAssess = () => {
    let vascularTxt =
        assessForm.value.vascularKind + " " + assessForm.value.vascularEval;
    if (
        assessForm.value.vascularKind === "Permcath" &&
        assessForm.value.permcathLen
    ) {
        vascularTxt = `Permcath(${assessForm.value.permcathLen}cm) ${assessForm.value.vascularEval}`;
    }

    let skinTxt = assessForm.value.skinState;
    if (
        assessForm.value.skinState !== "完整" &&
        assessForm.value.skinLocation
    ) {
        skinTxt = `${assessForm.value.skinLocation} ${assessForm.value.skinSize}cm ${assessForm.value.skinState}`;
    }

    store.assessState = {
        vascular: vascularTxt,
        conscious: assessForm.value.consciousState,
        skin: skinTxt,
    };

    if (assessForm.value.note) {
        store.addNursingRecord(
            `[評估備註] 血管/意識/皮膚綜合追加：${assessForm.value.note}`,
        );
    }

    showAssessModal.value = false;
};

// 新增透前給藥行
const addPreDrugRow = () => {
    const name = prompt("請輸入藥品處置名稱：", "Amtrel 5mg");
    if (name) {
        const time = new Date().toTimeString().slice(0, 5);
        preDrugs.value.push({
            text: `@${time} ${name} PO 楚心瑜`,
            isDeleted: false,
        });
    }
};

// 刪除透前給藥 (加線留痕行為)
const deletePreDrug = (idx) => {
    preDrugs.value[idx].isDeleted = true;
};

// 執行簽章與雙人連動
const executeMainOnSign = () => {
    if (isSignDisabled.value) return;
    store.mainSigned = !store.mainSigned;
    if (store.mainSigned) {
        store.addNursingRecord(
            `[上針簽章] 主責護理師楚心瑜完成當班 On-Sign 具名核簽，生理參數與過磅記錄上鎖。`,
        );
    }
};

const triggerDoubleSign = () => {
    isDoubleSigned.value = !isDoubleSigned.value;
    if (isDoubleSigned.value) {
        store.addNursingRecord(
            `[雙人雙簽] 雙簽核對護理師王曉明臨櫃覆核通過完成 Double Sign。`,
        );
    }
};

// 監聽選中病患切換
watch(
    () => store.currentPatient.bed,
    () => {
        isDoubleSigned.value = false;
    },
    { deep: true },
);
</script>

<style scoped>
/* ════ 100% 導入原稿 hismp_nursing_ui_v24.html 核心樣式 ════ */
.onsign-tab-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    box-sizing: border-box;
}

.confirm-bar {
    background: #f0fdf4;
    border: 1.5px solid #86efac;
    border-radius: 7px;
    padding: 6px 10px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    cursor: pointer;
    color: #15803d;
}
.sec-title {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 3px;
}
.vsign-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 3px;
    margin-bottom: 8px;
}
.vsign-box {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 6px;
    padding: 5px;
    text-align: center;
    cursor: pointer;
}
.vsign-box:hover {
    border-color: #99f6e4;
}
.vsign-l {
    font-size: 9px;
    color: #94a3b8;
    margin-bottom: 1px;
}
.vsign-v {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.text-warn {
    color: #b45309;
}

.assess-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
    margin-bottom: 8px;
}
.assess-box {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    padding: 6px 7px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
}
.assess-lbl {
    font-size: 10px;
    font-weight: 700;
    color: #475569;
}
.assess-val {
    font-size: 10px;
    color: #16a34a;
    font-weight: 600;
    flex: 1;
    margin: 0 4px;
}

.drug-section-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
}
.add-drug-btn {
    font-size: 10px;
    color: #0f766e;
    background: #f0fdfa;
    border: 1.5px dashed #99f6e4;
    border-radius: 6px;
    padding: 3px 9px;
    cursor: pointer;
}
.pre-drug-item {
    flex: 1;
    background: #f0fdfa;
    border: 1.5px solid #99f6e4;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-width: 200px;
}
.pre-drug-item.deleted {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #94a3b8;
}
.delete-drug-x {
    background: none;
    border: none;
    cursor: pointer;
    color: #b91c1c;
    font-weight: 700;
    margin-left: 4px;
}
.action-note-tag {
    font-size: 9px;
    color: #b91c1c;
    font-style: italic;
}

.hep-readonly {
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    border-radius: 7px;
    padding: 7px 10px;
    margin: 7px 0;
}
.hep-ro-title {
    font-size: 10px;
    font-weight: 700;
    color: #7c3aed;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.hep-drug-cols {
    display: flex;
    gap: 6px;
}
.hep-drug-col {
    flex: 1;
    background: white;
    border: 1px solid #ddd6fe;
    border-radius: 5px;
    padding: 6px 8px;
    text-align: center;
}
.hep-drug-name {
    font-size: 12px;
    font-weight: 700;
    color: #7c3aed;
    margin-bottom: 3px;
}
.hep-drug-dose {
    font-size: 11px;
    color: #334155;
}

.sign-row {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}
.sbtn {
    flex: 1;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 5px;
    text-align: center;
    font-size: 11px;
    cursor: pointer;
    color: #475569;
    background: white;
    font-weight: 700;
    transition: all 0.2s;
}
.sbtn.signed {
    background: #f0fdf4;
    border-color: #86efac;
    color: #15803d;
}

/* 彈窗對齊樣式 */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    z-index: 500;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open {
    display: flex;
}
.modal {
    background: white;
    border-radius: 13px;
    padding: 16px;
    width: 90%;
    max-width: 520px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-height: 88vh;
    overflow-y: auto;
    position: relative;
    text-align: left;
}
.modal-hdr {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 11px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.modal-x {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #b91c1c;
    border: none;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.prep-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    margin-bottom: 4px;
    font-size: 12px;
    cursor: pointer;
}
.prep-item.checked {
    background: #f0fdf4;
    border-color: #86efac;
}
.prep-chk {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 700;
}
.prep-item.checked .prep-chk {
    background: #16a34a;
    border-color: #16a34a;
    color: white;
    font-size: 10px;
}

/* 統一評估分頁樣式 */
.assess-tab-btn {
    flex: 1;
    padding: 6px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    background: white;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.assess-tab-btn.active {
    border-color: #0f766e;
    background: #0f766e;
    color: white;
}
.assess-opt-row {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 8px;
}
.assess-opt {
    font-size: 11px;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 3px 10px;
    cursor: pointer;
    background: white;
    color: #475569;
    user-select: none;
}
.assess-opt.sel {
    background: #0f766e;
    color: white;
    border-color: #0f766e;
}

.form-label {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
    display: block;
}
.form-input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    padding: 7px 9px;
    font-size: 12px;
    outline: none;
    margin-bottom: 8px;
    box-sizing: border-box;
}
.form-input:focus {
    border-color: #14b8a6;
}
.form-row {
    display: flex;
    gap: 8px;
}
.form-col {
    flex: 1;
}

.mbtn-row {
    display: flex;
    gap: 6px;
    margin-top: 10px;
    justify-content: flex-end;
}
.mbtn {
    border: none;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.mbtn.sec {
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #e2e8f0;
}
.mbtn.pri {
    background: #0f766e;
    color: white;
}
.animate-pulse {
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    50% {
        opacity: 0.6;
    }
}
</style>

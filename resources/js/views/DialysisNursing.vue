<template>
    <!-- V20 RWD：遮罩 + 抽屜按鈕（完全對齊原稿 DL-124） -->
    <div
        id="drw-overlay"
        :class="{ show: store.isLeftDrawerOpen && isMobileOrTablet }"
        @click="store.isLeftDrawerOpen = false"
    ></div>
    <button
        id="drw-btn"
        :style="drawerBtnStyle"
        @click="store.isLeftDrawerOpen = !store.isLeftDrawerOpen"
    >
        {{ store.isLeftDrawerOpen ? "☰ 收合" : "☰ 病患" }}
    </button>

    <!-- 💡 RWD 模式下選填的頂部病患快選膠囊條 (對齊原稿 DL-124) -->
    <div id="pill-bar" :style="{ display: isMobileOrTablet ? 'flex' : 'none' }">
        <div
            class="bed-pill"
            style="background: #0f766e"
            @click="
                store.selectPatient({
                    bed: '01',
                    name: '薛玉鳳',
                    mr: 'MR9876543',
                })
            "
        >
            01 薛玉鳳
        </div>
        <div
            class="bed-pill"
            style="background: #b91c1c"
            @click="
                store.selectPatient({
                    bed: '02',
                    name: '林*芳',
                    mr: 'MR223344',
                })
            "
        >
            02 林*芳
        </div>
        <div
            class="bed-pill"
            style="background: #d97706"
            @click="
                store.selectPatient({
                    bed: '05',
                    name: '李*美',
                    mr: 'MR445566',
                })
            "
        >
            05 李*美
        </div>
        <div
            class="bed-pill"
            style="background: #7c3aed"
            @click="
                store.selectPatient({
                    bed: '07',
                    name: '陳*志',
                    mr: 'MR556677',
                })
            "
        >
            07 陳*志
        </div>
    </div>

    <!-- ════ 主彈性佈局容器 (.main) ════ -->
    <div class="main">
        <!-- 🟢 左半部大外殼容器（內含側拉面板、今日照護清單） -->
        <div class="left-outer" :style="leftOuterResponsiveStyle">
            <PatientList />
        </div>

        <!-- 🟢 右半部大外殼容器 (.right) -->
        <div class="right" :style="rightResponsiveStyle">
            <!-- ① 右半部頂部病患抬頭列 (.r-hdr) -->
            <div class="r-hdr">
                <div>
                    <div
                        style="
                            display: flex;
                            align-items: baseline;
                            gap: 6px;
                            flex-wrap: wrap;
                        "
                    >
                        <span
                            class="r-bed"
                            id="r-bed"
                            :style="{ display: isMobileOrTablet ? 'none' : '' }"
                            >床 {{ store.currentPatient.bed }}</span
                        >
                        <span
                            class="r-pt-name"
                            id="r-ptname"
                            style="font-size: clamp(14px, 3vw, 22px)"
                            >{{ store.currentPatient.name }}</span
                        >
                        <span
                            class="r-mr"
                            id="r-mr"
                            style="
                                font-size: clamp(10px, 1.5vw, 13px);
                                color: var(--slate-lt);
                            "
                            >{{ store.currentPatient.mr }}</span
                        >
                    </div>
                </div>
                <div class="r-hdr-actions">
                    <button class="print-btn">
                        <i class="ti ti-printer"></i>📄 列印病患聯絡溝通標籤
                    </button>
                    <button class="incident-btn">
                        <i class="ti ti-bolt"></i>⚡ 臨床突發事件
                    </button>
                    <button
                        class="emg-btn"
                        id="emg-btn-hdr"
                        style="font-size: 11px; padding: 5px 10px"
                    >
                        <i class="ti ti-alert-triangle"></i>🚨 EMERGENCY
                    </button>
                </div>
            </div>

            <!-- ② Off-Sign 完成後稽核修改提示條 (.edit-notice-bar) -->
            <div
                class="edit-notice-bar"
                :class="{
                    show:
                        store.activeTab === 2 &&
                        store.currentPatient.bed === '03',
                }"
            >
                <i class="ti ti-clock" style="flex-shrink: 0"></i>
                <span
                    >⏰ Off-Sign 完成 10:48 ・ 剩餘可修改時間：13 小時 12 分 ・
                    逾時永久上鎖 ・ 本次修改將記錄稽核紀錄</span
                >
            </div>

            <!-- ③ 中央網格大分割工作區 (.content-area) -->
            <div class="content-area">
                <!-- 過磅扣重機台固定欄 (動態收合寬度對齊 22px / 27%) -->
                <div
                    class="fixed-col"
                    :class="{ collapsed: store.isFixedColCollapsed }"
                >
                    <PatientFixedInfo />
                </div>

                <!-- 三大核心步驟動態流程頁籤區 (.tab-col) -->
                <div class="tab-col">
                    <div class="tabs">
                        <div
                            :class="['tab', { active: store.activeTab === 0 }]"
                            @click="store.activeTab = 0"
                        >
                            💉 上針與雙簽
                        </div>
                        <div
                            :class="['tab', { active: store.activeTab === 1 }]"
                            @click="store.activeTab = 1"
                        >
                            📊 四小時監控
                        </div>
                        <div
                            :class="['tab', { active: store.activeTab === 2 }]"
                            @click="store.activeTab = 2"
                        >
                            🏁 安全下機
                        </div>
                    </div>

                    <div class="tab-content">
                        <TabOnSign v-if="store.activeTab === 0" />
                        <TabMonitoring v-if="store.activeTab === 1" />
                        <TabOffSign v-if="store.activeTab === 2" />
                    </div>
                </div>
            </div>

            <!-- ④ 下方常駐副大看板（一般筆電模式並排顯示） -->
            <div v-if="!isLandscapeTablet" class="nursing-record-bar-block">
                <NursingRecordBar />
            </div>

            <div v-if="!isLandscapeTablet" class="order-pool-block">
                <OrderPool />
            </div>
        </div>
    </div>

    <!-- ════ 5. 平板橫向專用：Bottom Sheet 拉籤式 (完全對齊原稿 DL-146) ════ -->
    <div
        v-if="isLandscapeTablet"
        :class="['bs-dim', { show: currentBsOpen !== null }]"
        @click="closeAllBS"
    ></div>

    <!-- 護理記錄 Sheet -->
    <div
        v-if="isLandscapeTablet"
        :class="['bs-sheet', { open: currentBsOpen === 'nr' }]"
    >
        <div class="bs-tab-row">
            <div class="bs-spacer-l"></div>
            <button class="bs-tab bs-tab-nr" @click="toggleBS('nr')">
                📋 護理記錄
            </button>
            <div class="bs-spacer-m"></div>
            <div style="width: 80px"></div>
            <div class="bs-spacer-r"></div>
        </div>
        <div class="bs-sheet-body">
            <NursingRecordBar />
        </div>
    </div>

    <!-- 醫囑池 Sheet -->
    <div
        v-if="isLandscapeTablet"
        :class="['bs-sheet', { open: currentBsOpen === 'op' }]"
    >
        <div class="bs-tab-row">
            <div class="bs-spacer-l"></div>
            <div style="width: 80px"></div>
            <div class="bs-spacer-m"></div>
            <button class="bs-tab bs-tab-op" @click="toggleBS('op')">
                💊 醫囑池
            </button>
            <div class="bs-spacer-r"></div>
        </div>
        <div class="bs-sheet-body">
            <OrderPool />
        </div>
    </div>

    <!-- 底部觸發列（收合時並列拉籤） -->
    <div
        v-if="isLandscapeTablet"
        :class="['bs-trigger-bar', { 'hide-bar': currentBsOpen !== null }]"
    >
        <div class="bs-trigger-row">
            <div class="bs-spacer-l"></div>
            <button class="bs-trigger bs-trigger-nr" @click="toggleBS('nr')">
                📋 護理記錄
            </button>
            <div class="bs-spacer-m"></div>
            <button class="bs-trigger bs-trigger-op" @click="toggleBS('op')">
                💊 醫囑池
            </button>
            <div class="bs-spacer-r"></div>
        </div>
    </div>

    <AbsenceLeaveModal
        v-model="activeModals.absence"
        :patient="modalTargetPatient"
        @confirm="handleAbsenceSubmit"
    />

    <ProtectiveEquipmentModal
        v-model="activeModals.equipment"
        @confirm="handleNursingRecordSubmit"
    />

    <NurseWatchingModal
        v-model="activeModals.nw"
        @confirm="handleNursingRecordSubmit"
    />

    <AddNursingRecordModal
        v-model="activeModals.record"
        @confirm="handleNursingRecordSubmit"
    />

    <WeightDeductionModal v-model="activeModals.deduct" />

    <ExtraMeasurementModal
        v-model="activeModals.extra"
        @confirm="handleExtraGridRowSubmit"
    />
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, reactive } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

// 🧱 引入剛寫好的 6 大 RESTful 對齊功能彈窗元件
import AbsenceLeaveModal from "../components/nurse/modals/AbsenceLeaveModal.vue";
import ProtectiveEquipmentModal from "../components/nurse/modals/ProtectiveEquipmentModal.vue";
import NurseWatchingModal from "../components/nurse/modals/NurseWatchingModal.vue";
import AddNursingRecordModal from "../components/nurse/modals/AddNursingRecordModal.vue";
import WeightDeductionModal from "../components/nurse/modals/WeightDeductionModal.vue";
import ExtraMeasurementModal from "../components/nurse/modals/ExtraMeasurementModal.vue";

import PatientList from "../components/nurse/PatientList.vue";
import PatientFixedInfo from "../components/nurse/PatientFixedInfo.vue";
import TabOnSign from "../components/nurse/TabOnSign.vue";
import TabMonitoring from "../components/nurse/TabMonitoring.vue";
import TabOffSign from "../components/nurse/TabOffSign.vue";
import NursingRecordBar from "../components/nurse/NursingRecordBar.vue";
import OrderPool from "../components/nurse/OrderPool.vue";

const store = useDialysisStore();

// 🎛️ 彈窗反應式狀態開關集
const activeModals = reactive({
    absence: false,
    equipment: false,
    nw: false,
    record: false,
    deduct: false,
    extra: false,
});

// 視窗大小即時監測
const windowWidth = ref(window.innerWidth);
const windowHeight = ref(window.innerHeight);
const currentBsOpen = ref(null);

const handleResize = () => {
    windowWidth.value = window.innerWidth;
    windowHeight.value = window.innerHeight;
};

onMounted(() => {
    window.addEventListener("resize", handleResize);
    // 初始化時如果解析度本來就小，依據原稿規則自動將過磅側欄收合
    if (windowWidth.value < 1024) {
        store.isFixedColCollapsed = true;
    }

    store.fetchTodayShiftPatients();
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
});

// 🟢 事件 1：處理 PatientList 拋出的長按請假假單請求
const triggerAbsenceLeave = (pt) => {
    modalTargetPatient.value = pt;
    activeModals.absence = true;
};

// 🟢 事件 2：具名核准假單，向 Laravel RESTful [POST] 發送流轉
const handleAbsenceSubmit = async (formData) => {
    if (!modalTargetPatient.value) return;
    const success = await store.processPatientAbsence(
        modalTargetPatient.value,
        formData.status,
        formData.note,
    );
    if (success) {
        alert(
            `✅ 假單辦理完成！病患 [${modalTargetPatient.value.name}] 狀態流轉成功，系統已強制自動完帳關閉。`,
        );
    }
};

// 🟢 事件 3：通用護理記錄持久化，連動後端 RESTful [POST] 節點
const handleNursingRecordSubmit = (contentText) => {
    store.addNursingRecord(contentText);
};

// 🟢 事件 4：追加監控網格動態临时加測抄表時段
const handleExtraGridRowSubmit = (extraData) => {
    // 將時段推入本地 careSign 網格中，並即時向後端發送同步
    const timeKey = extraData.target_time;
    alert(
        `📊 臨時加測已成立！系統成功於監控網格插入 [${timeKey}] 時段行。\n原因：${extraData.reason}`,
    );

    // 連動在護理記錄留下一筆追蹤軌軌跡
    store.addNursingRecord(
        `[網格加測] 於監控網格中動態追加 [${timeKey}] 時段追蹤行。原因：${extraData.reason}`,
    );
};

// 🟢 醫療安全防線：點擊上針 On-Sign 簽章大鈕 (對接 DL-128 血糖防漏鎖)
const savePreVitals = async () => {
    // 包裹當前生理徵象數值與血糖醫囑檢驗
    const payload = {
        has_fs_order: store.currentPatient.hasFSOrder,
        fs: store.vsignData.fs,
        bp: store.vsignData.bp,
        pr: store.vsignData.pr,
    };

    try {
        // 調用大腦，傳送給 Laravel 進行臨床審查
        const res = await axios.post(
            `/api/v1/patients/${store.currentPatient.mr}/vitals`,
            payload,
        );
        store.mainSigned = true;
        alert(res.data.message);
        store.addNursingRecord(
            `[臨床雙簽] 完成透前生理徵象核對與 On-Sign 具名雙簽章。`,
        );
    } catch (err) {
        // 如果觸發了後端 Controller 開好的 DL-128 血糖漏鎖攔截錯誤 (422)
        if (
            err.response &&
            err.response.data &&
            err.response.data.error_code === "DL-128"
        ) {
            alert(err.response.data.message); // 噴出高強度法規阻擋攔截彈窗
        } else {
            alert("⚠️ 臨床簽章送出失敗，請確認生理常規數值是否輸入完整。");
        }
    }
};

// ════ RWD 橫向/縱向平板高精準判定 (DL-124) ════
const isMobileOrTablet = computed(() => windowWidth.value < 1024);
const isLandscapeTablet = computed(
    () => windowWidth.value < 1024 && windowWidth.value > windowHeight.value,
);

// 左側抽屜大框架響應式內嵌 CSS 還原
const leftOuterResponsiveStyle = computed(() => {
    if (!isMobileOrTablet.value) return {}; // 筆電桌機模式保持原樣

    // 平板模式下：left-outer 寬度強制設為 0，改為內部 fixed 側滑抽屜結構
    return {
        width: "0px",
        minWidth: "0px",
        flexShrink: 0,
        overflow: "visible",
        position: "relative",
    };
});

// 右側工作視窗響應式內嵌 CSS 還原
const rightResponsiveStyle = computed(() => {
    if (!isMobileOrTablet.value) return {};
    return {
        flex: 1,
        display: "flex",
        flexDirection: "column",
        overflowY: "auto",
        minHeight: 0,
        overflowX: "hidden",
    };
});

// RWD ☰ 彈出抽屜按鈕位置跟隨移動
const drawerBtnStyle = computed(() => {
    if (!isMobileOrTablet.value) return { display: "none" };
    return {
        display: "flex",
        left: store.isLeftDrawerOpen ? "305px" : "0px",
    };
});

// 監聽抽屜開啟，對齊原稿直滑手感
watch(
    () => store.isLeftDrawerOpen,
    (isOpen) => {
        const leftSidebar = document.querySelector(".left");
        if (leftSidebar) {
            leftSidebar.style.transform = isOpen
                ? "translateX(0px)"
                : "translateX(-305px)";
        }
    },
);

// ════ Bottom Sheet 控制項 ════
const toggleBS = (type) => {
    currentBsOpen.value = currentBsOpen.value === type ? null : type;
};
const closeAllBS = () => {
    currentBsOpen.value = null;
};
</script>

<style scoped>
/* ════ 100% 複製原稿 V24 全網格 CSS 環境變數與底層樣式 ════ */
.main {
    display: flex;
    flex: 1;
    overflow: hidden;
    height: 100%;
}

/* 左側總框架容器 */
.left-outer {
    display: flex;
    flex-shrink: 0;
    width: 30%;
    min-width: 260px;
    position: relative;
    height: 100%; /* 🌟 關鍵：強制讓左側大盒子等於 .main 的實體高度，不准往外爆 */
}

/* ⚠️ 核心重構：讓內嵌的 .left 子元件在 RWD 模式下自動繼承原稿的 Fixed 特效 */
:deep(.left) {
    transition: transform 0.25s ease;
}
@media (max-width: 1023.98px) {
    :deep(.left) {
        position: fixed !important;
        top: 46px;
        left: 0;
        bottom: 0;
        width: 305px !important;
        z-index: 300;
        transform: translateX(-305px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: white;
    }
    :deep(.left-scroll) {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
}

/* 右半部外殼容器 */
.right {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
    min-height: 0;
}

/* 右半部頂端抬頭狀態列 */
.r-hdr {
    background: white;
    border-bottom: 2px solid #e2e8f0;
    padding: 7px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
.r-bed {
    color: #0f766e;
    font-size: 18px;
    font-weight: 700;
}
.r-pt-name {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}
.r-mr {
    font-size: 11px;
    color: #475569;
    margin-top: 1px;
}
.r-hdr-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.print-btn {
    background: white;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}
.incident-btn {
    background: #7c3aed;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}
.emg-btn {
    background: #b91c1c;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    animation: pulse-red 2s infinite;
}
@keyframes pulse-red {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(185, 28, 28, 0.5);
    }
    60% {
        box-shadow: 0 0 0 6px rgba(185, 28, 28, 0);
    }
}

/* 歸檔修改警告橫條 */
.edit-notice-bar {
    background: #fffbeb;
    border-bottom: 1.5px solid #fde68a;
    padding: 4px 12px;
    font-size: 11px;
    color: #b45309;
    display: none;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}
.edit-notice-bar.show {
    display: flex;
}

/* 中央內容網格切割 */
.content-area {
    display: flex;
    flex: 1;
    overflow: hidden;
    position: relative;
}
.fixed-col {
    width: 27%;
    min-width: 185px;
    background: white;
    border-right: 2px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    position: relative;
    transition:
        width 0.25s,
        min-width 0.25s;
    overflow: hidden;
}
.fixed-col.collapsed {
    width: 22px;
    min-width: 22px;
}

.tab-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
}
.tabs {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    background: #f1f5f9;
    flex-shrink: 0;
}
.tab {
    flex: 1;
    padding: 8px 4px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    color: #475569;
    border-bottom: 3px solid transparent;
}
.tab.active {
    color: #0f766e;
    background: white;
    border-bottom-color: #0f766e;
}
.tab-content {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.nursing-record-bar-block {
    background: white;
    border-bottom: 1.5px solid #cbd5e1; /* 對齊原稿區隔線 */
    flex-shrink: 0;
    box-sizing: border-box;
}

.order-pool-block {
    background: white;
    flex-shrink: 0;
    box-sizing: border-box;
}

/* ════ RWD 平板抽屜與並列拉籤樣式環境 ════ */
#drw-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 299;
}
#drw-overlay.show {
    display: block;
}
#drw-btn {
    display: none;
    position: fixed;
    top: 50%;
    z-index: 301;
    background: #0f766e;
    color: white;
    border: none;
    border-radius: 0 8px 8px 0;
    padding: 10px 7px;
    cursor: pointer;
    font-size: 13px;
    writing-mode: vertical-lr;
    font-weight: 700;
    letter-spacing: 1px;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
    transition: left 0.25s ease;
    transform: translateY(-50%);
}

#pill-bar {
    display: none;
    overflow-x: auto;
    padding: 4px 6px;
    gap: 5px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    -webkit-overflow-scrolling: touch;
    flex-shrink: 0;
    z-index: 21;
}
.bed-pill {
    flex-shrink: 0;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    color: white;
    cursor: pointer;
    white-space: nowrap;
}

/* ════ Bottom Sheet 拉籤樣式繪製 (DL-146) ════ */
.bs-sheet {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    transform: translateY(100%);
    transition: transform 0.28s ease;
    z-index: 500;
    background: transparent;
    display: flex;
    flex-direction: column;
    max-height: 50vh;
}
.bs-sheet.open {
    transform: translateY(0);
}
.bs-tab-row {
    display: flex;
    background: transparent;
    pointer-events: none;
    flex-shrink: 0;
    height: 34px;
}
.bs-tab {
    height: 34px;
    padding: 0 20px;
    font-size: 11px;
    font-weight: 700;
    color: white;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    pointer-events: all;
    border-radius: 10px 10px 0 0;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.bs-tab-nr {
    background: #0f766e;
}
.bs-tab-op {
    background: #7c3aed;
}
.bs-spacer-l {
    flex: 1;
}
.bs-spacer-m {
    width: 12px;
}
.bs-spacer-r {
    flex: 1;
}
.bs-sheet-body {
    flex: 1;
    overflow-y: auto;
    padding: 8px 12px;
    font-size: 11px;
    background: white;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.18);
}

.bs-trigger-bar {
    display: block;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 34px;
    z-index: 499;
    background: transparent;
    pointer-events: none;
}
.bs-trigger-bar.hide-bar {
    display: none !important;
}
.bs-trigger-row {
    display: flex;
    height: 34px;
    pointer-events: none;
}
.bs-trigger {
    height: 34px;
    padding: 0 20px;
    font-size: 11px;
    font-weight: 700;
    color: white;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    pointer-events: all;
    border-radius: 10px 10px 0 0;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.bs-trigger-nr {
    background: #0f766e;
}
.bs-trigger-op {
    background: #7c3aed;
}
.bs-dim {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 498;
    background: rgba(0, 0, 0, 0.1);
}
.bs-dim.show {
    display: block;
}

.action-trigger-btn {
    background: #0f766e;
    color: white;
    border: none;
    font-size: 11px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    margin: 10px 0;
    transition: background 0.15s;
}
.action-trigger-btn:hover {
    background: #134e4a;
}
</style>

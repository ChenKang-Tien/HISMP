<template>
    <!-- 100% 還原原稿大外殼結構層級 -->
    <div class="left-sidebar-inner">
        <!-- ════ 1. Off-Sign 已完成病患側拉條與飛出面板 (完全對齊原稿) ════ -->
        <div
            class="offsign-strip"
            @click="isFlyoutOpen = !isFlyoutOpen"
            title="Off-Sign 已完成病患（24h 內可修改）"
        >
            <div class="strip-badge">✅{{ offsignPatients.length }}</div>
            <div class="strip-label">
                {{ isFlyoutOpen ? "◀ 收合" : "▶ Off-Sign" }}
            </div>
        </div>

        <div :class="['offsign-flyout', { open: isFlyoutOpen }]">
            <div class="flyout-hdr">✅ Off-Sign 已完成（24h 內可修改）</div>
            <div class="offsign-edit-notice">
                <i class="ti ti-alert-triangle alert-icon"></i>
                點入病患可修改，系統記錄所有修改行為（5W1H）
            </div>

            <!-- 已下機病患卡 (卡片 03) -->
            <div
                v-for="pt in offsignPatients"
                :key="pt.mr"
                class="ptcard offsigned"
                @click="selectOffsignPatient(pt)"
                style="flex-direction: column"
            >
                <div class="ptcard-body" style="width: 100%">
                    <div class="prow1">
                        <span class="bed-no gray">{{ pt.bed }}</span>
                        <span class="mr-no">{{ pt.mr }}</span>
                        <span class="pt-name">{{ pt.name }}</span>
                        <span
                            style="
                                font-size: 9px;
                                background: #f0fdf4;
                                color: #15803d;
                                border: 1px solid #86efac;
                                border-radius: 10px;
                                padding: 1px 6px;
                                flex-shrink: 0;
                            "
                            >✅</span
                        >
                    </div>
                    <div class="sband done">
                        ✅ 已下機 10:48 ・ 剩餘可修改：13h 12m
                    </div>
                    <div class="lock-notice" style="margin: 4px 8px 6px">
                        <i class="ti ti-lock"></i> 10:48 Off-Sign ・ 超過 24h
                        後永久上鎖
                    </div>
                </div>
            </div>
        </div>

        <!-- ════ 2. 今日照護病患主清單 (.left) ════ -->
        <div class="left">
            <div class="left-hdr">
                <div class="left-hdr-title">
                    <i class="ti ti-users"></i> 今日照護病患
                </div>
                <div class="shift-selector">
                    <span
                        v-for="s in ['早班', '午班', '晚班', '全院']"
                        :key="s"
                        :class="['shift-btn', { active: store.currentShiftFilter === s }]"
                        @click="store.setShiftFilter(s)"
                    >
                        {{ s }}
                    </span>
                </div>
            </div>

            <div class="left-scroll" :key="store.currentShiftFilter">
                <div v-for="group in store.filteredGroups" :key="group.name">
                    <!-- 組別分隔線 (完全對齊原稿樣式) -->
                    <div class="group-divider">
                        <div
                            class="group-dot"
                            :style="{ backgroundColor: group.color }"
                        ></div>
                        {{ group.name }}（{{ group.patients.length }}位）{{
                            group.isMine ? "・我的組別" : ""
                        }}
                    </div>

                    <!-- 病患卡片主體 -->
                    <div
                        v-for="pt in group.patients"
                        :key="pt.mr"
                        :id="'card' + pt.bed"
                        :class="[
                            'ptcard',
                            {
                                active: store.currentPatient.bed === pt.bed,
                                crit: pt.isCrit,
                            },
                        ]"
                        @click="store.selectPatient(pt)"
                        @mousedown="startLongPress($event, pt)"
                        @mouseup="cancelLongPress"
                        @mouseleave="cancelLongPress"
                        @touchstart="startLongPress($event, pt)"
                        @touchend="cancelLongPress"
                    >
                        <div
                            class="grp-bar"
                            :style="{ backgroundColor: group.color }"
                        ></div>

                        <div class="ptcard-body">
                            <!-- prow1 -->
                            <div class="prow1">
                                <span
                                    :class="[
                                        'bed-no',
                                        {
                                            red: pt.isCrit,
                                            amber: pt.bed === '05',
                                        },
                                    ]"
                                    >{{ pt.bed }}</span
                                >
                                <span class="mr-no">{{ pt.mr }}</span>
                                <span class="pt-name">{{ pt.name }}</span>

                                <span
                                    class="pt-hct"
                                    style="
                                        display: flex;
                                        gap: 2px;
                                        flex-shrink: 0;
                                    "
                                >
                                    <span
                                        v-if="pt.hct"
                                        style="
                                            font-size: 9px;
                                            background: #f0fdfa;
                                            border: 1px solid #99f6e4;
                                            border-radius: 3px;
                                            padding: 1px 5px;
                                            color: #0f766e;
                                            cursor: pointer;
                                        "
                                        @click.stop="emit('open-modal', 'hct', pt)"
                                    >
                                        TW:{{ pt.hct }}
                                    </span>
                                </span>

                                <!-- Nurse Watching -->
                                <span
                                    class="cam-btn"
                                    v-if="pt.hasNW"
                                    @click.stop="emit('open-modal', 'nw', pt)"
                                >
                                    📷 NW
                                </span>

                                <!-- 醫囑數量 -->
                                <span
                                    class="order-badge"
                                    v-if="pt.orderCount > 0"
                                >
                                    醫囑
                                    <span class="order-badge-num">{{
                                        pt.orderCount
                                    }}</span>
                                </span>
                            </div>

                            <!-- 狀態提示條 (動態顯示班別與狀態) -->
                            <div
                                :class="['sband', pt.isCrit ? 'crit' : 'dial']"
                            >
                                {{ pt.statusText }}
                            </div>

                            <!-- 補回原稿未測 HCT 代替警告條 (薛玉鳳專用) -->
                            <div
                                class="alert-item fallback"
                                v-if="pt.bed === '01'"
                            >
                                <i class="ti ti-alert-triangle"></i>⚠️ 本週未測
                                HCT，以 LW 值（32.5%）代替
                            </div>

                            <!-- 實驗室危急回報通知 (DL-126) -->
                            <div
                                v-if="pt.bed === '01'"
                                class="lab-alert-bar"
                                @click.stop="showLabAlert"
                            >
                                🧪 檢驗超標：K⁺ 6.1（↑）・ HGB 9.1（↓）・
                                點入查看
                            </div>

                            <!-- 生理數據四維大看盤 -->
                            <div class="vit-row">
                                <div class="vit">
                                    <div class="vit-l">血壓</div>
                                    <div
                                        class="vit-v"
                                        :class="{ red: pt.isCrit }"
                                    >
                                        {{
                                            pt.bed === "01" && store.vsignFilled
                                                ? store.vsignData.bp
                                                : pt.vitals.bp
                                        }}
                                    </div>
                                </div>
                                <div class="vit">
                                    <div class="vit-l">脈搏</div>
                                    <div class="vit-v">
                                        {{
                                            pt.bed === "01" && store.vsignFilled
                                                ? store.vsignData.pr
                                                : pt.vitals.pr
                                        }}
                                    </div>
                                </div>
                                <div class="vit">
                                    <div class="vit-l">F/S血糖</div>
                                    <div
                                        class="vit-v"
                                        :class="{ warn: pt.bed === '01' }"
                                    >
                                        {{
                                            pt.bed === "01" && store.vsignFilled
                                                ? store.vsignData.fs
                                                : pt.vitals.fs
                                        }}
                                    </div>
                                </div>
                                <div class="vit">
                                    <div class="vit-l">血流速</div>
                                    <div class="vit-v">{{ pt.vitals.qb }}</div>
                                </div>
                            </div>

                            <!-- 透析流程進度條 -->
                            <div class="pt-prog">
                                <span class="prog-lbl">透析</span>
                                <div class="prog-bar">
                                    <div
                                        class="prog-fill"
                                        :style="{
                                            width: pt.progress + '%',
                                            backgroundColor: pt.isCrit
                                                ? '#b91c1c'
                                                : '#14b8a6',
                                        }"
                                    ></div>
                                </div>
                                <span class="prog-pct">{{ pt.progress }}%</span>
                            </div>

                            <!-- 下拉電子病歷快捷抽屜 (DL-119) -->
                            <div
                                class="drawer-toggle"
                                @click.stop="toggleDrawer(pt.bed)"
                            >
                                <span>🔓 病患資料抽屜 ▼</span>
                                <i class="ti ti-chevron-down"></i>
                            </div>
                            <div
                                :class="[
                                    'drawer-body',
                                    { open: openDrawers.has(pt.bed) },
                                ]"
                            >
                                <span class="dbtn" @click.stop="openDetail('basicInfo', pt)">🪪 基本資料</span>
                                <span class="dbtn" @click.stop="openDetail('orderSheet', pt)">📝 醫囑單</span>
                                <span class="dbtn" @click.stop="openDetail('vascular', pt)">🌿 血管通路</span>
                                <span class="dbtn" @click.stop="openDetail('anemia', pt)">🩸 貧血治療</span>
                                <span class="dbtn" @click.stop="openDetail('lab', pt)">🧪 檢驗記錄</span>
                                <span class="dbtn" @click.stop="openDetail('longterm', pt)">📋 長期醫囑</span>
                                <span
                                    class="dbtn"
                                    style="
                                        background: #f0fdfa;
                                        border-color: #0f766e;
                                        color: #0f766e;
                                        font-weight: 700;
                                    "
                                    @click.stop="emit('open-modal', 'dialysisRecord', pt)"
                                >📋 透析記錄單</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ════ 3. OFF SIGN 請假/住院子分區 (完全還原原稿 V19 DL-122) ════ -->
            <div class="absence-subzone-hdr">
                <span>📋 請假／住院</span>
                <span class="absence-sub-count">{{
                    absentPatientsList.length
                }}</span>
            </div>
            <div class="absence-subzone-body">
                <div
                    v-if="absentPatientsList.length === 0"
                    class="absence-empty-msg"
                >
                    本日無請假或住院病患
                </div>

                <div
                    v-for="pt in absentPatientsList"
                    :key="pt.mr"
                    class="ptcard absent-card"
                >
                    <div class="absent-overlay">
                        {{ pt.status === "LEAVE" ? "請假" : "住院" }}
                    </div>
                    <div class="ptcard-body">
                        <div class="prow1">
                            <span class="bed-no gray">{{ pt.bed }}</span>
                            <span class="mr-no">{{ pt.mr }}</span>
                            <span class="pt-name">{{ pt.name }}</span>
                        </div>
                        <div class="sband done">
                            病患已{{ pt.status === "LEAVE" ? "請假" : "住院" }}
                            ・ 視同 OFF SIGN
                        </div>
                    </div>
                </div>
            </div>

            <!-- 明日整備排班區 -->
            <div class="tomorrow-zone">
                <div class="tmr-hdr" @click="isTmrOpen = !isTmrOpen">
                    <i class="ti ti-calendar-event"></i>📅 明日排班與前置整備
                    <i
                        :class="[
                            'ti',
                            'ti-chevron-down',
                            'tmr-icon',
                            { rotated: isTmrOpen },
                        ]"
                    ></i>
                </div>
                <div class="tmr-body" v-if="isTmrOpen">
                    <button
                        class="tmr-btn"
                        @click="emit('open-modal', 'nursingShift')"
                    >
                        <i class="ti ti-users"></i> 護理師明日排班名單
                    </button>
                    <button
                        class="tmr-btn green"
                        @click="emit('open-modal', 'supplyTmr')"
                    >
                        <i class="ti ti-package"></i> 🚀 明日庫房領料大總表
                    </button>
                </div>
            </div>
        </div>

        <!-- 假單審查對話盒 -->
        <NurseWatchingModal
            :model-value="props.activeModals.nw"
            @update:model-value="(v) => props.activeModals.nw = v"
        />
        <div :class="['modal-overlay', { open: showAbsenceModal }]">
            <div class="modal" style="max-width: 360px">
                <div class="modal-hdr">
                    <i class="ti ti-user-off"></i>病患狀態調整
                </div>
                <div class="absence-pt-focus" v-if="longPressTarget">
                    病患：{{ longPressTarget.name }}
                </div>
                <div class="absence-btn-grid">
                    <button
                        :class="[
                            'absence-type-btn',
                            { sel: selectedAbsenceType === 'LEAVE' },
                        ]"
                        @click="selectedAbsenceType = 'LEAVE'"
                    >
                        🏖️<br />請假
                    </button>
                    <button
                        :class="[
                            'absence-type-btn',
                            { sel: selectedAbsenceType === 'HOSPITALIZED' },
                        ]"
                        @click="selectedAbsenceType = 'HOSPITALIZED'"
                    >
                        🏥<br />住院
                    </button>
                </div>
                <div class="form-group">
                    <label class="form-label">備註原因 (選填)</label>
                    <input
                        v-model="absenceNote"
                        type="text"
                        class="form-input"
                        placeholder="如：感冒就醫、心臟科住院..."
                    />
                </div>
                <div class="mbtn-row">
                    <button class="mbtn sec" @click="closeAbsenceModal">
                        取消
                    </button>
                    <button
                        class="mbtn pri"
                        :disabled="!selectedAbsenceType"
                        @click="confirmAbsence"
                    >
                        確認
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const emit = defineEmits(['open-modal']);
const props = defineProps({ activeModals: Object });
const store = useDialysisStore();

const isFlyoutOpen = ref(false);
const isTmrOpen = ref(false);
const openDrawers = ref(new Set());
import NurseWatchingModal from "@/components/nurse/modals/NurseWatchingModal.vue";
const showAbsenceModal = ref(false);

let longPressTimer = null;
const longPressTarget = ref(null);
const selectedAbsenceType = ref(null);
const absenceNote = ref("");

const patientGroups = ref([
    {
        name: "A 組・楚心瑜護理師",
        color: "#0f766e",
        isMine: true,
        patients: [
            {
                bed: "01",
                mr: "MR9876543",
                name: "薛玉鳳",
                status: "DIAL",
                statusText: "🟢 透析中 ・ 已透 2h 24m ・ 🎯 UF 3.50kg",
                orderCount: 2,
                hasNW: true,
                progress: 60,
                isCrit: false,
                vitals: { bp: "135/82", pr: "78", fs: "142", qb: "250" },
            },
            {
                bed: "02",
                mr: "MR223344",
                name: "林*芳",
                status: "CRIT",
                statusText: "🔴 危急 ・ 血壓 190/110",
                orderCount: 1,
                hct: "31.5",
                hasNW: false,
                progress: 40,
                isCrit: true,
                vitals: { bp: "190/110", pr: "88", fs: "118", qb: "230" },
            },
            {
                bed: "05",
                mr: "MR445566",
                name: "李*美",
                status: "DIAL",
                statusText: "🟢 透析中 ・ 已透 3h 32m ・ 🎯 UF 2.20kg",
                orderCount: 2,
                hct: "33.2",
                hasNW: true,
                progress: 88,
                isCrit: false,
                vitals: { bp: "110/70", pr: "70", fs: "98", qb: "250" },
            },
        ],
    },
    {
        name: "B 組・王曉明護理師",
        color: "#7c3aed",
        isMine: false,
        patients: [
            {
                bed: "07",
                mr: "MR556677",
                name: "陳*志",
                status: "DIAL",
                statusText: "🟢 透析中 ・ 已透 1h 45m",
                orderCount: 0,
                hasNW: false,
                progress: 35,
                isCrit: false,
                vitals: { bp: "128/78", pr: "72", fs: "105", qb: "240" },
            },
        ],
    },
]);

const absentPatientsList = ref([]);
const offsignPatients = ref([{ bed: "03", mr: "MR334455", name: "張*華" }]);

const filteredGroups = computed(() => {
    const query = store.searchQuery.trim().toLowerCase();
    if (!query) return patientGroups.value;

    return patientGroups.value
        .map((group) => {
            const matchedPatients = group.patients.filter(
                (pt) =>
                    pt.name.toLowerCase().includes(query) ||
                    pt.mr.toLowerCase().includes(query) ||
                    pt.bed.toLowerCase().includes(query),
            );
            return { ...group, patients: matchedPatients };
        })
        .filter((group) => group.patients.length > 0);
});

const toggleDrawer = (bed) => {
    if (openDrawers.value.has(bed)) openDrawers.value.delete(bed);
    else openDrawers.value.add(bed);
};

const openDetail = async (type, pt) => {
    const data = await store.fetchPatientDetails(pt.mr, type);
    console.log(`[Drawer] 開啟 ${type} 資料:`, data);
    // 此處可連接到對應 Modal，若無則暫時 log
    emit('open-modal', type, pt, data);
};

const startLongPress = (event, pt) => {
    longPressTarget.value = pt;
    longPressTimer = setTimeout(() => {
        selectedAbsenceType.value =
            pt.status === "LEAVE" || pt.status === "HOSPITALIZED"
                ? pt.status
                : null;
        showAbsenceModal.value = true;
    }, 500);
};

const cancelLongPress = () => {
    if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
};
const closeAbsenceModal = () => {
    showAbsenceModal.value = false;
    longPressTarget.value = null;
};

const confirmAbsence = () => {
    if (!longPressTarget.value || !selectedAbsenceType.value) return;
    const pt = longPressTarget.value;
    const label = selectedAbsenceType.value === "LEAVE" ? "請假" : "住院";

    store.addNursingRecord(
        `病患辦理${label}，因特殊主訴調整本班次，系統已啟動強制作業自動完成當班 OFF SIGN。備註：${absenceNote.value || "無"}`,
    );
    pt.status = selectedAbsenceType.value;

    patientGroups.value.forEach((group) => {
        group.patients = group.patients.filter((p) => p.mr !== pt.mr);
    });

    absentPatientsList.value.push(pt);
    closeAbsenceModal();
    alert(`✅ 已成功核准並記錄 ${pt.name} 醫療次${label}變更。`);
};

const selectOffsignPatient = (pt) => {
    store.selectPatient(pt);
    store.activeTab = 2;
    isFlyoutOpen.value = false;
};

const showNWAlert = (hasRec) => {
    if (!hasRec) {
        alert("📷 本病患尚無 Nurse Watching 記錄");
        return;
    }
    alert("📷 開啟 Nurse Watching 歷史傷口影像病歷面板");
};

const showLabAlert = () => {
    alert(
        "📋 檢驗超標通知\n\nK⁺（鉀）：6.1 mEq/L（正常 3.5~5.0）⚠️ 超標\nHGB：9.1 g/dL（正常 10~13）⚠️ 偏低\n\n建議護理衛教：限制高鉀食物（香蕉、橙汁、番茄等）",
    );
};
</script>

<style scoped>
/* 100% 複製原稿與你微調後最完美的比例 CSS 變數 */
.left-sidebar-inner {
    display: flex;
    height: 100%;
    position: relative;
    width: 100%;
}
/* 側邊 Off-Sign 窄條固定 22px */
.offsign-strip {
    width: 22px;
    background: #f0fdf4;
    border-right: 1px solid #86efac;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 8px;
    cursor: pointer;
    flex-shrink: 0;
    gap: 4px;
    z-index: 12;
}
.offsign-strip:hover {
    background: #86efac;
}
.strip-badge {
    background: #16a34a;
    color: white;
    font-size: 9px;
    font-weight: 700;
    min-width: 16px;
    height: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.strip-label {
    font-size: 9px;
    color: #15803d;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    white-space: nowrap;
    font-weight: 600;
    letter-spacing: 0.4px;
}

/* 飛出面板 */
.offsign-flyout {
    position: absolute;
    left: 22px;
    top: 0;
    bottom: 0;
    width: 220px;
    background: white;
    border-right: 2px solid #86efac;
    z-index: 11;
    overflow-y: auto;
    padding: 8px 6px;
    box-shadow: 4px 0 16px rgba(0, 0, 0, 0.12);
    transform: translateX(-110%);
    transition: transform 0.25s ease;
}
.offsign-flyout.open {
    transform: translateX(0);
}
.flyout-hdr {
    font-size: 10px;
    font-weight: 700;
    color: #15803d;
    padding: 3px 5px 5px;
    border-bottom: 1px solid #86efac;
    margin-bottom: 6px;
}
.offsign-edit-notice {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 10px;
    color: #b45309;
    margin-bottom: 8px;
    display: flex;
    gap: 4px;
}

/* 🌟 今日病患清單區 (.left 主體)：吃滿 30% 外殼大空間大方舒展 🌟 */
.left {
    flex: 1;
    min-width: 0;
    background: white;
    border-right: 2px solid #e2e8f0;
    display: flex;
    flex-direction: column; /* 🟩 啟用垂直 Flex，將 Header、Scroll、Tomorrow 串起來 */
    height: 100%; /* 🌟 關鍵：強制高度等於外殼的 100%，不准無限長大 */
    overflow: hidden; /* 🌟 關鍵：阻止 Header 和 Tomorrow 被捲走 */
}

.left-hdr {
    padding: 7px 9px;
    border-bottom: 1.5px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.left-hdr-title {
    font-size: 10px;
    font-weight: 700;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 4px;
}
.shift-selector {
    display: flex;
    gap: 3px;
}
.shift-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #94a3b8;
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 20px;
    cursor: pointer;
}
.shift-btn.active {
    background: #f0fdfa;
    border-color: #99f6e4;
    color: #0f766e;
    font-weight: 600;
}
.left-scroll {
    flex: 1; /* 自動侵佔剩餘的所有空間 */
    min-height: 0; /* 🟩 這是 Flex 專屬防線！解鎖瀏覽器縮展限制，防止把下方擠出螢幕 */
    overflow-y: auto; /* 超出高度時，內部自動浮現網格滾動條 */
    padding: 8px 6px;
}
.group-divider {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 6px;
    margin: 6px 0 3px;
    background: #f1f5f9;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 700;
    color: #475569;
}
.group-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* 病患卡片主要骨架 */
.ptcard {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 7px;
    overflow: hidden;
    cursor: pointer;
    background: white;
    display: flex;
    position: relative;
    user-select: none;
}
.ptcard:hover {
    border-color: #99f6e4;
}
.ptcard.active {
    border-color: #0f766e;
    box-shadow: 0 0 0 3px #f0fdfa;
}
.ptcard.crit {
    border-color: #fecaca;
    animation: crit-flash 1s step-end infinite;
}
@keyframes crit-flash {
    0%,
    100% {
        border-color: #fecaca;
    }
    50% {
        border-color: #fde68a;
    }
}
.grp-bar {
    width: 5px;
    flex-shrink: 0;
    border-radius: 3px 0 0 3px;
}
.ptcard-body {
    flex: 1;
    min-width: 0;
    padding: 6px 8px;
}
.prow1 {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}
.bed-no {
    background: #0f766e;
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 5px;
    min-width: 30px;
    text-align: center;
    flex-shrink: 0;
}
.bed-no.amber {
    background: #d97706;
}
.bed-no.red {
    background: #b91c1c;
}
.bed-no.gray {
    background: #94a3b8;
}
.pt-name {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    flex: 1; /* ✨ 關鍵修正：自動撐滿中間剩餘空間，將後方組件逼到最右邊 */
    min-width: 45px; /* 防止文字被過度擠壓 */
}
.mr-no {
    font-size: 10px;
    color: #94a3b8;
}
.hct-chip {
    font-size: 9px;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 3px;
    padding: 1px 5px;
    color: #0f766e;
}
.cam-btn {
    font-size: 10px;
    color: #7c3aed;
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    border-radius: 4px;
    padding: 2px 5px;
    position: relative;
    flex-shrink: 0;
}
.nw-dot {
    position: absolute;
    top: -3px;
    right: -3px;
    width: 7px;
    height: 7px;
    background: #b91c1c;
    border-radius: 50%;
}
.order-badge {
    background: #b45309;
    color: white;
    font-size: 9px;
    font-weight: 700;
    border-radius: 10px;
    padding: 2px 7px;
    display: flex;
    align-items: center;
    gap: 3px;
    flex-shrink: 0;
}
.order-badge-num {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    padding: 0 4px;
}
.sband {
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 4px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 3px;
    border-top: 1px solid #e2e8f0;
}
.sband.dial {
    background: #f0fdfa;
    color: #0f766e;
}
.sband.crit {
    background: #fef2f2;
    color: #b91c1c;
}
.sband.done {
    background: #f0fdf4;
    color: #15803d;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    font-size: 10px;
    border-top: 1px solid #e2e8f0;
}
.alert-item.fallback {
    background: #fffbeb;
    color: #b45309;
    border-top: 1px solid #fde68a;
}
.lab-alert-bar {
    background: #fef3c7;
    color: #92400e;
    border-left: 3px solid #f59e0b;
    padding: 3px 6px;
    font-size: 10px;
    margin-top: 4px;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    box-sizing: border-box;
}

.vit-row {
    display: flex;
    border-top: 1px solid #f1f5f9;
    margin-top: 5px;
}
.vit {
    flex: 1;
    padding: 4px 2px;
    text-align: center;
    border-right: 1px solid #f1f5f9;
}
.vit:last-child {
    border-right: none;
}
.vit-l {
    font-size: 9px;
    color: #94a3b8;
}
.vit-v {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
}
.vit-v.red {
    color: #b91c1c;
}
.vit-v.warn {
    color: #b45309;
}

.pt-prog {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 4px;
    padding: 3px 8px;
    border-top: 1px solid #f1f5f9;
}
.prog-lbl {
    font-size: 9px;
    color: #94a3b8;
}
.prog-bar {
    flex: 1;
    height: 4px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}
.prog-fill {
    height: 100%;
    border-radius: 3px;
}
.prog-pct {
    font-size: 9px;
    color: #475569;
    width: 24px;
    text-align: right;
}

.drawer-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 3px 8px;
    color: #94a3b8;
    font-size: 10px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    margin-top: 4px;
    cursor: pointer;
}
.drawer-body {
    display: none;
    padding: 5px 8px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    gap: 3px;
    flex-wrap: wrap;
}
.drawer-body.open {
    display: flex;
}
.dbtn {
    font-size: 10px;
    color: #0f766e;
    background: white;
    border: 1px solid #99f6e4;
    border-radius: 5px;
    padding: 2px 7px;
    cursor: pointer;
}

/* 請假住院專區 */
.absence-subzone-hdr {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    padding: 4px 8px;
    background: #f1f5f9;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}
.absence-sub-count {
    background: #94a3b8;
    color: white;
    border-radius: 8px;
    padding: 1px 6px;
    font-size: 9px;
}
.absence-subzone-body {
    padding: 4px;
    min-height: 20px;
    background: #f8fafc;
}
.absence-empty-msg {
    text-align: center;
    font-size: 10px;
    color: #94a3b8;
    padding: 6px 0;
}
.absent-card {
    opacity: 0.5;
    filter: grayscale(80%);
    position: relative;
}
.absent-overlay {
    position: absolute;
    inset: 0;
    background: rgba(100, 116, 139, 0.18);
    border-radius: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    color: #475569;
    z-index: 2;
}

.tomorrow-zone {
    border-top: 2px solid #e2e8f0;
    background: #fffbeb;
    margin-top: auto; /* 🟩 強制將自己推到 Flex 容器的最底部 */
    flex-shrink: 0; /* 絕對不允許被壓縮變形 */
    z-index: 10;
}
.tmr-hdr {
    padding: 7px 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    color: #92400e;
}
.tmr-icon {
    margin-left: auto;
    transition: transform 0.2s;
}
.tmr-icon.rotated {
    transform: rotate(180deg);
}
.tmr-body {
    padding: 0 8px 8px;
}
.tmr-btn {
    display: flex;
    width: 100%;
    background: #0f766e;
    color: white;
    border: none;
    border-radius: 7px;
    padding: 7px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 5px;
    gap: 5px;
    align-items: center;
    justify-content: center;
}
.tmr-btn.green {
    background: #16a34a;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    z-index: 550;
    display: none;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open {
    display: flex;
}
.modal {
    background: white;
    border-radius: 12px;
    padding: 16px;
    width: 90%;
    max-width: 360px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}
.modal-hdr {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
}
.absence-pt-focus {
    font-size: 13px;
    font-weight: 700;
    color: #7c3aed;
    margin-bottom: 12px;
}
.absence-btn-grid {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}
.absence-type-btn {
    flex: 1;
    padding: 12px 8px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
}
.absence-type-btn.sel {
    border-color: #0f766e !important;
    background: #f0fdfa !important;
    color: #0f766e;
}
.form-group {
    margin-bottom: 12px;
}
.form-label {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    display: block;
    margin-bottom: 4px;
}
.form-input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 9px;
    font-size: 12px;
    outline: none;
}
.form-input:focus {
    border-color: #14b8a6;
}
.mbtn-row {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}
.mbtn {
    border: none;
    border-radius: 6px;
    padding: 8px 14px;
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
.mbtn.pri:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

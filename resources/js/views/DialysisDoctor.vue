<template>
    <div class="main-container" id="main-wrap">
        <!-- 左側病患側邊欄 (已完成) -->
        <PatientSidebar />

        <!-- 右側核心工作區 -->
        <div class="right" v-if="store.currentPatient">
            <!-- 1. 頂部決策與 KPI 數據列 -->
            <DecisionRow />

            <!-- 2. Pill 床號快速切換橫條 -->
            <!-- <div class="pill-bar">
                <span
                    style="
                        font-size: 10px;
                        color: var(--slate-lt);
                        white-space: nowrap;
                    "
                    >切換：</span
                >
                <button
                    v-for="p in store.patients.filter(
                        (p) => p.status !== 'visited',
                    )"
                    :key="p.id"
                    class="pill-btn"
                    :class="
                        p.bedColor === 'red'
                            ? 'r'
                            : p.bedColor === 'amber'
                              ? 'a'
                              : 'p'
                    "
                    :style="
                        store.selectedPatientId === p.id
                            ? 'box-shadow: 0 0 0 3px var(--pri-bd); border: 1px solid var(--white);'
                            : 'opacity: 0.6;'
                    "
                    @click="store.selectPatient(p.id)"
                >
                    {{ p.bedNo }}
                </button>
            </div> -->

            <!-- 3. Quick Action Tags 快捷工具列 -->
            <div class="tags-row">
                <button class="tag-btn" @click="store.openPopout('pop-flow')">
                    <i class="ti ti-activity-heartbeat"></i> 血流速偵測
                </button>
                <button class="tag-btn" @click="store.openPopout('pop-access')">
                    <i class="ti ti-needle"></i> 血管通路
                </button>
                <button class="tag-btn" @click="store.openPopout('pop-anemia')">
                    <i class="ti ti-droplet"></i> 貧血治療記錄
                </button>
                <button class="tag-btn" @click="store.openPopout('pop-lab')">
                    <i class="ti ti-flask"></i> 檢驗記錄
                </button>
                <button class="tag-btn" @click="store.openPopout('pop-order')">
                    <i class="ti ti-prescription"></i> 醫囑單
                </button>
                <button class="tag-btn" @click="store.openPopout('pop-pn')">
                    <i class="ti ti-notes"></i> Progress Notes
                </button>
                <button class="tag-btn" @click="store.openPopout('pop-basic')">
                    <i class="ti ti-id-badge"></i> 基本資料
                </button>
                <button
                    class="tag-btn"
                    @click="store.openPopout('pop-handover')"
                >
                    <i class="ti ti-message-2"></i> 交接班記錄
                </button>
            </div>

            <!-- 4. 中央數據網格 -->
            <MonitoringGrid />

            <!-- 5. 下層整合開單工作台 -->
            <WorkspaceConsole />
        </div>
    </div>

    <!-- 全域集中式彈窗層 -->
    <DoctorModals />
</template>

<script setup>
import { useDoctorStore } from "@/store/doctorStore";
import PatientSidebar from "@/components/doctor/PatientSidebar.vue";
import DecisionRow from "../components/doctor/DecisionRow.vue";
import MonitoringGrid from "../components/doctor/MonitoringGrid.vue";
import WorkspaceConsole from "../components/doctor/WorkspaceConsole.vue";
import DoctorModals from "../components/doctor/DoctorModals.vue";

const store = useDoctorStore();
</script>

<style scoped>
/* 🎨 V39 官方色彩調色盤變數精確注入 🎨 */
:deep(:root),
.main-container {
    --pri: #1d4ed8;
    --pri2: #3b82f6;
    --pri-lt: #eff6ff;
    --pri-bd: #bfdbfe;
    --pri-dk: #1e3a8a;
    --green: #166534;
    --green2: #16a34a;
    --green-lt: #f0fdf4;
    --green-bd: #86efac;
    --slate: #475569;
    --slate-lt: #94a3b8;
    --slate-ul: #f1f5f9;
    --slate-bg: #f0f4f8;
    --white: #fff;
    --border: #e2e8f0;
    --teal: #0f766e;
    --teal-lt: #f0fdfa;
    --teal-bd: #99f6e4;
    --amber: #b45309;
    --amber-lt: #fef3c7;
    --amber-bd: #fde68a;
    --red: #b91c1c;
    --red-lt: #fef2f2;
    --red-bd: #fecaca;
    --purple: #7c3aed;
    --purple-lt: #f5f3ff;
    --purple-bd: #ddd6fe;
}

.main-container {
    display: flex;
    flex: 1;
    overflow: hidden;
    height: calc(100vh - 46px);
    background: var(--slate-bg); /* 還原 V39 背景底色 */
}

.right {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--white);
}

/* Pill 快速切換條與配色還原 */
.pill-bar {
    display: flex;
    gap: 6px;
    padding: 7px 14px 7px 20px;
    background: white;
    border-bottom: 1.5px solid var(--border);
    overflow-x: auto;
    align-items: center;
    flex-shrink: 0;
}
.pill-btn {
    border: none;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    color: white;
}
.pill-btn.p {
    background: var(--pri);
}
.pill-btn.r {
    background: var(--red);
}
.pill-btn.a {
    background: #d97706;
}

/* 快捷按鈕樣式 */
.tags-row {
    display: flex;
    gap: 5px;
    padding: 5px 12px;
    background: var(--slate-ul);
    border-bottom: 1px solid var(--border);
    overflow-x: auto;
    flex-shrink: 0;
}
.tag-btn {
    background: white;
    border: 1.5px solid var(--border);
    color: var(--slate);
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    gap: 4px;
}
.tag-btn:hover {
    border-color: var(--pri);
    color: var(--pri);
}
</style>

<template>
    <div class="decision-row" v-if="store.currentPatient">
        <div class="dr-top">
            <div class="pt-title-block">
                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        flex-wrap: wrap;
                    "
                >
                    <span class="pt-title">
                        {{ store.currentPatient.name }}
                        <span
                            style="
                                font-size: 14px;
                                color: var(--slate-lt);
                                font-weight: 400;
                            "
                            >床 {{ store.currentPatient.bedNo }}</span
                        >
                    </span>
                    <span style="font-size: 11px; color: var(--slate-lt)">
                        {{ store.currentPatient.chartNo }} ·
                        {{ store.currentPatient.meta }}
                    </span>
                    <div class="dx-inline">
                        <span
                            v-for="dx in store.currentPatient.tags"
                            :key="dx"
                            class="dx-tag diag"
                            >{{ dx }}</span
                        >
                        <span
                            v-for="allg in store.currentPatient.allergies"
                            :key="allg"
                            class="dx-tag allergy"
                        >
                            ⚠️ 過敏：{{ allg }}
                        </span>
                    </div>
                </div>

                <div
                    style="display: inline-flex; align-items: center; gap: 2px"
                >
                    <button
                        @click="store.shiftVisitDate(-1)"
                        class="date-nav-btn"
                    >
                        &lt;
                    </button>
                    <button
                        class="date-display-btn"
                        @click="store.openModal('history')"
                    >
                        <i class="ti ti-calendar"></i>
                        <span>{{ store.visitDate }}</span>
                    </button>
                    <button
                        @click="store.shiftVisitDate(1)"
                        class="date-nav-btn"
                    >
                        &gt;
                    </button>
                </div>
            </div>

            <button
                class="emg-btn"
                :class="{
                    'emg-active': store.currentPatient.status === 'crit',
                }"
                @click="store.toggleEmergency()"
            >
                <i class="ti ti-urgent"></i> EMERGENCY
            </button>
            <button
                class="finish-btn"
                @click="store.openModal('modal-normal-confirm')"
            >
                <i class="ti ti-check"></i> 確認完成巡床
            </button>
        </div>

        <div class="kpi-strip">
            <div class="kpi-card warn" style="min-width: 120px">
                <div class="kc-label">HCT</div>
                <div
                    style="
                        display: flex;
                        align-items: baseline;
                        gap: 6px;
                        justify-content: center;
                    "
                >
                    <div style="text-align: center">
                        <div style="font-size: 9px; color: var(--slate-lt)">
                            LW 上周
                        </div>
                        <div class="kc-val" style="font-size: 16px">32.4</div>
                    </div>
                    <div style="color: var(--slate-lt); font-size: 12px">/</div>
                    <div style="text-align: center">
                        <div style="font-size: 9px; color: var(--slate-lt)">
                            TW 這周
                        </div>
                        <div
                            class="kc-val"
                            style="font-size: 16px; color: var(--amber)"
                        >
                            30.1
                        </div>
                    </div>
                    <div style="color: var(--slate-lt); font-size: 12px">/</div>
                    <div style="text-align: center">
                        <div style="font-size: 9px; color: var(--slate-lt)">
                            AT 確認
                        </div>
                        <div
                            class="kc-val"
                            style="font-size: 16px; color: var(--slate-lt)"
                        >
                            —
                        </div>
                    </div>
                </div>
            </div>
            <div class="kpi-card ok">
                <div class="kc-label">Hb(M)</div>
                <div class="kc-val">
                    {{ store.currentPatient.kpiMini?.hb || "—" }}
                </div>
                <div class="kc-sub">g/dL 月</div>
            </div>
            <div class="kpi-card ok">
                <div class="kc-label">Ferritin(Q)</div>
                <div class="kc-val">186</div>
                <div class="kc-sub">ng/mL 季</div>
            </div>
            <div class="kpi-card warn">
                <div class="kc-label">Kt/V</div>
                <div class="kc-val">
                    {{ store.currentPatient.kpiMini?.ktv || "1.15" }}
                </div>
                <div class="kc-sub">Daugirdas</div>
            </div>
            <div class="kpi-card ok">
                <div class="kc-label">URR</div>
                <div class="kc-val">68.2</div>
                <div class="kc-sub">%</div>
            </div>
            <div class="kpi-card ok">
                <div class="kc-label">Ca×P</div>
                <div class="kc-val">42.1</div>
                <div class="kc-sub">mg²/dL²</div>
            </div>
            <div class="kpi-card clickable" @click="store.openModal('drywt')">
                <div class="kc-label">乾體重 ✏️</div>
                <div class="kc-val">
                    {{ store.currentPatient.dryWeight || "59.5" }}
                </div>
                <div class="kc-sub">kg</div>
            </div>
            <div class="kpi-card clickable">
                <div class="kc-label">F/S</div>
                <div class="kc-val">186</div>
                <div class="kc-sub">mg/dL</div>
            </div>
            <div
                class="kpi-card nursing clickable"
                @click="store.openModal('nursingSummary')"
            >
                <div class="kc-label">📋 今日護理摘要</div>
                <div class="kc-body">
                    透前：Heparin 已給<br />透中：第2H 血壓稍高，已處置
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useDoctorStore } from "@/store/doctorStore";
const store = useDoctorStore();
</script>

<style scoped>
.decision-row {
    background: white;
    border-bottom: 1.5px solid var(--border);
    padding: 8px 12px;
    flex-shrink: 0;
}
.dr-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}
.pt-title-block {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    flex: 1;
}
.pt-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
}
.dx-inline {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    align-items: center;
}
.dx-tag {
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.dx-tag.diag {
    background: var(--pri-lt);
    color: var(--pri);
}
.dx-tag.allergy {
    background: var(--red-lt);
    color: var(--red);
    border: 1px solid var(--red-bd);
}
.date-nav-btn {
    background: none;
    border: 1px solid var(--border);
    border-radius: 4px;
    color: var(--pri);
    cursor: pointer;
    font-size: 14px;
    padding: 1px 7px;
}
.date-display-btn {
    background: var(--pri-lt);
    border: 1px solid var(--pri-bd);
    color: var(--pri);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
}
.finish-btn {
    background: var(--green);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    margin-left: auto;
}
.emg-btn {
    background: var(--slate-lt);
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
}
.emg-btn.emg-active {
    background: var(--red);
    animation: crit-flash 1s step-end infinite;
}
.kpi-strip {
    display: flex;
    gap: 5px;
    overflow-x: auto;
    padding-bottom: 2px;
    flex-wrap: nowrap;
}
.kpi-card {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 5px 10px;
    text-align: center;
    flex-shrink: 0;
    min-width: 68px;
}
.kpi-card .kc-label {
    font-size: 9px;
    color: var(--slate-lt);
    font-weight: 600;
}
.kpi-card .kc-val {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.kpi-card .kc-sub {
    font-size: 9px;
    color: var(--slate-lt);
}
.kpi-card.ok {
    border-color: var(--green-bd);
    background: var(--green-lt);
}
.kpi-card.ok .kc-val {
    color: var(--green);
}
.kpi-card.warn {
    border-color: var(--amber-bd);
    background: var(--amber-lt);
}
.kpi-card.warn .kc-val {
    color: var(--amber);
}
.kpi-card.clickable {
    cursor: pointer;
    border-color: var(--pri-bd);
    background: var(--pri-lt);
}
.kpi-card.nursing {
    min-width: 180px;
    text-align: left;
    cursor: pointer;
}
.kpi-card.nursing .kc-body {
    font-size: 11px;
    color: #334155;
    line-height: 1.5;
    margin-top: 3px;
}
@keyframes crit-flash {
    0%,
    100% {
        background: var(--red);
    }
    50% {
        background: #d97706;
    }
}
</style>

<template>
    <div class="left" id="left-panel">
        <div class="left-hdr">
            <div id="left-hdr-content">
                <div class="left-hdr-top">
                    <span class="left-hdr-title"
                        ><i class="ti ti-users"></i> 今日病患</span
                    >
                    <div class="shift-selector">
                        <button
                            v-for="shift in ['早班', '午班', '晚班', '全院']"
                            :key="shift"
                            :class="[
                                'shift-btn',
                                { active: store.currentShift === shift },
                            ]"
                            @click="store.currentShift = shift"
                        >
                            {{ shift }}
                        </button>
                    </div>
                </div>
                <div class="search-mini">
                    <i class="ti ti-search" style="color: var(--slate-lt)"></i>
                    <input
                        v-model="store.searchQuery"
                        placeholder="快速篩選..."
                    />
                </div>
            </div>
        </div>

        <div class="left-scroll">
            <div
                v-for="patient in store.activePatients"
                :key="patient.id"
                :class="[
                    'ptcard',
                    {
                        active: store.selectedPatientId === patient.id,
                        crit: patient.status === 'crit',
                    },
                ]"
                :style="
                    patient.status === 'crit' ? 'border-color: #fca5a5;' : ''
                "
                @click="store.selectPatient(patient.id)"
            >
                <div class="prow1">
                    <span :class="['bed-no', patient.bedColor]">
                        {{ patient.bedNo }}
                    </span>

                    <div class="pt-name-block">
                        <div class="pt-name">{{ patient.name }}</div>
                        <div class="pt-meta">
                            {{ patient.chartNo }} · {{ patient.meta }}
                        </div>
                    </div>

                    <div class="wt-inline">
                        <div class="wt-chip" v-if="patient.wtChip.pre">
                            <div class="wl">透前</div>
                            <div class="wv">{{ patient.wtChip.pre }}</div>
                        </div>
                        <div class="wt-chip" v-if="patient.wtChip.preUf">
                            <div class="wl">預脫水</div>
                            <div class="wv">{{ patient.wtChip.preUf }}</div>
                        </div>
                        <div
                            class="wt-chip"
                            v-if="
                                patient.wtChip.preAdj !== '—' &&
                                patient.wtChip.preAdj
                            "
                        >
                            <div class="wl">預調水</div>
                            <div class="wv">{{ patient.wtChip.preAdj }}</div>
                        </div>
                        <div
                            :class="[
                                'wt-chip',
                                { pending: patient.wtChip.post.includes('⏳') },
                            ]"
                            v-if="
                                patient.wtChip.post !== '—' &&
                                patient.wtChip.post
                            "
                        >
                            <div class="wl">透後</div>
                            <div
                                class="wv"
                                :style="
                                    patient.wtChip.post.includes('⏳')
                                        ? 'color: var(--slate-lt); font-weight: 400;'
                                        : ''
                                "
                            >
                                {{ patient.wtChip.post }}
                            </div>
                        </div>
                    </div>

                    <span
                        class="nw-btn"
                        @click.stopPropagation="console.log('開啟 NW彈窗')"
                    >
                        Nurse Watching
                        <span v-if="patient.id === '205'" class="nw-dot"></span>
                    </span>
                </div>

                <div
                    :class="[
                        'sband',
                        patient.status === 'crit'
                            ? 'crit'
                            : patient.status === 'wait'
                              ? 'wait'
                              : 'dial',
                    ]"
                >
                    <i
                        :class="[
                            'ti',
                            patient.status === 'crit'
                                ? 'ti-alert-triangle'
                                : patient.status === 'wait'
                                  ? 'ti-clock'
                                  : 'ti-activity',
                        ]"
                    ></i>
                    {{ patient.statusText }}
                </div>

                <div class="progress-row" v-if="patient.status !== 'wait'">
                    <div class="prog-bar">
                        <div
                            class="prog-fill"
                            :style="{
                                width: patient.progress + '%',
                                background:
                                    patient.status === 'crit'
                                        ? '#b91c1c'
                                        : '#0f766e',
                            }"
                        ></div>
                    </div>
                    <span class="prog-text">{{ patient.timeText }}</span>
                </div>

                <div class="kpi-row">
                    <div
                        v-for="(val, key) in patient.kpiMini"
                        :key="key"
                        :class="[
                            'kpi-mini',
                            {
                                alert:
                                    patient.status === 'crit' ||
                                    (key === 'bp' && patient.id === '108'),
                                warn:
                                    key === 'extraLabel' &&
                                    patient.id === '108',
                            },
                        ]"
                    >
                        <div class="kl">
                            {{
                                key === "extraLabel"
                                    ? patient.kpiMini.extraLabel
                                    : key.toUpperCase()
                            }}
                        </div>
                        <div
                            class="kv"
                            :style="
                                patient.status === 'crit' ||
                                (key === 'bp' && patient.id === '108')
                                    ? 'color: #b91c1c;'
                                    : ''
                            "
                        >
                            {{ val }}
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="visited-toggle"
                :class="{ open: store.isVisitedExpanded }"
                id="visitedToggle"
                @click="store.toggleVisitedSection"
            >
                <i class="ti ti-chevron-right arr"></i>
                <span>已巡視 ({{ store.visitedPatients.length }})</span>
                <span
                    style="
                        font-size: 10px;
                        color: var(--slate-lt);
                        margin-left: auto;
                    "
                    >點擊展開</span
                >
            </div>

            <div
                class="visited-section"
                :class="{ open: store.isVisitedExpanded }"
                id="visitedSection"
            >
                <div class="visited-search">
                    <i
                        class="ti ti-search"
                        style="color: var(--slate-lt); font-size: 12px"
                    ></i>
                    <input placeholder="搜尋已巡視病患..." />
                </div>

                <div
                    v-for="patient in store.visitedPatients"
                    :key="patient.id"
                    class="ptcard visited"
                    @click="store.selectPatient(patient.id)"
                >
                    <div class="prow1">
                        <span class="bed-no gray">{{ patient.bedNo }}</span>
                        <div class="pt-name-block">
                            <div class="pt-name" style="color: var(--slate)">
                                {{ patient.name }}
                            </div>
                            <div class="pt-meta">
                                {{ patient.chartNo }} · {{ patient.meta }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="sband dial"
                        style="
                            background: #f1f5f9;
                            color: var(--slate-lt);
                            border-top: 1px solid #e2e8f0;
                        "
                    >
                        <i
                            class="ti ti-circle-check-filled"
                            style="color: #16a34a"
                        ></i>
                        已巡視
                        <i
                            class="ti ti-lock"
                            style="
                                margin-left: auto;
                                font-size: 11px;
                                color: var(--slate-lt);
                            "
                        ></i>
                    </div>
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
/* 🎨 V39 官方色彩調色盤變數注入，確保與全域外殼無縫接軌 🎨 */
:deep(:root),
.left {
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
    --red-bd: #fca5a5;
    --purple: #7c3aed;
    --purple-lt: #f5f3ff;
    --purple-bd: #ddd6fe;
}

.left {
    width: 30%;
    min-width: 260px;
    background: var(--white);
    border-right: 2px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    flex-shrink: 0;
}

.left-hdr {
    padding: 8px 10px;
    border-bottom: 1.5px solid var(--border);
    flex-shrink: 0;
    background: var(--white);
}

.left-hdr-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.left-hdr-title {
    font-size: 10px;
    font-weight: 700;
    color: var(--slate);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.shift-selector {
    display: flex;
    gap: 3px;
}

.shift-btn {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    color: var(--slate-lt);
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.15s;
}

.shift-btn.active {
    background: var(--pri-lt);
    border-color: var(--pri-bd);
    color: var(--pri);
    font-weight: 600;
}

.search-mini {
    display: flex;
    align-items: center;
    gap: 5px;
    background: var(--slate-ul);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px 8px;
}

.search-mini input {
    background: transparent;
    border: none;
    outline: none;
    font-size: 11px;
    flex: 1;
    color: #1e293b;
}

.left-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 8px 6px;
    background: #f8fafc; /* 貼合工作台淡底色 */
}

/* 病患卡片 */
.ptcard {
    border: 2px solid var(--border);
    border-radius: 10px;
    margin-bottom: 7px;
    overflow: hidden;
    cursor: pointer;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
    background: var(--white);
}

.ptcard:hover {
    border-color: var(--pri-bd);
}

/* Active 活化卡片：醫師藍高亮外框 */
.ptcard.active {
    border-color: var(--pri) !important;
    box-shadow: 0 0 0 3px var(--pri-lt);
}

/* 🚨 危急病患閃爍動畫紅框 */
.ptcard.crit {
    border-color: var(--red-bd);
    animation: crit-flash 1.2s step-end infinite;
}

@keyframes crit-flash {
    0%,
    100% {
        border-color: var(--red-bd);
        box-shadow: 0 0 4px rgba(185, 28, 28, 0.1);
    }
    50% {
        border-color: #ef4444;
        box-shadow: 0 0 8px rgba(185, 28, 28, 0.25);
    }
}

.prow1 {
    display: flex;
    align-items: flex-start;
    padding: 6px 8px;
    gap: 6px;
    flex-wrap: wrap;
}

/* 床號標籤精準配色 */
.bed-no {
    background: var(--pri);
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 5px;
    min-width: 34px;
    text-align: center;
    flex-shrink: 0;
}
.bed-no.amber {
    background: #d97706 !important;
}
.bed-no.red {
    background: var(--red) !important;
}
.bed-no.gray {
    background: var(--slate-lt) !important;
}

.pt-name-block {
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.pt-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.pt-meta {
    font-size: 10px;
    color: var(--slate-lt);
}

.wt-inline {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    flex: 1;
}

.wt-chip {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    border-radius: 5px;
    padding: 2px 6px;
    font-size: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1.3;
}

.wt-chip .wl {
    font-size: 9px;
    color: var(--slate-lt);
}

.wt-chip .wv {
    font-weight: 700;
    color: #1e293b;
}

/* Nurse Watching 紫色小按鈕 */
.nw-btn {
    font-size: 10px;
    color: var(--purple);
    background: var(--purple-lt);
    border: 1px solid var(--purple-bd);
    border-radius: 4px;
    padding: 2px 5px;
    cursor: pointer;
    flex-shrink: 0;
    white-space: nowrap;
    position: relative;
    margin-left: auto;
    font-weight: 600;
}

.nw-dot {
    position: absolute;
    top: -3px;
    right: -3px;
    width: 7px;
    height: 7px;
    background: var(--red);
    border-radius: 50%;
    animation: blink 0.8s step-end infinite;
}

@keyframes blink {
    50% {
        opacity: 0.2;
    }
}

/* 狀態色帶 */
.sband {
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 3px;
    border-top: 1px solid var(--border);
}

.sband.dial {
    background: var(--teal-lt);
    color: var(--teal);
}
.sband.wait {
    background: var(--amber-lt);
    color: #92400e;
}
.sband.crit {
    background: var(--red-lt);
    color: var(--red);
}

.progress-row {
    padding: 4px 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.prog-bar {
    flex: 1;
    height: 5px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
}

.prog-fill {
    height: 100%;
    border-radius: 3px;
}

.prog-text {
    font-size: 10px;
    color: var(--slate-lt);
    font-family: monospace;
}

/* KPI 迷你條帶 */
.kpi-row {
    display: flex;
    gap: 4px;
    padding: 4px 8px 6px;
    overflow-x: auto;
}

.kpi-mini {
    background: var(--slate-ul);
    border-radius: 5px;
    padding: 3px 7px;
    text-align: center;
    flex-shrink: 0;
    border: 1px solid transparent;
}

.kpi-mini .kl {
    font-size: 9px;
    color: var(--slate-lt);
}

.kpi-mini .kv {
    font-size: 11px;
    font-weight: 700;
    color: #1e293b;
}

.kpi-mini.alert {
    background: var(--red-lt);
    border-color: #fca5a5;
}

.kpi-mini.warn {
    background: var(--amber-lt);
    border-color: #fde68a;
}
.kpi-mini.warn .kv {
    color: var(--amber);
}

/* 已巡視區塊 */
.visited-toggle {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 6px 8px;
    cursor: pointer;
    background: var(--slate-ul);
    border-radius: 6px;
    margin: 10px 0 5px 0;
    font-size: 11px;
    color: var(--slate);
    font-weight: 700;
    border: 1px solid var(--border);
}

.visited-toggle i.arr {
    transition: transform 0.2s ease;
    color: var(--slate-lt);
}

.visited-toggle.open i.arr {
    transform: rotate(90deg);
}

.visited-section {
    display: none;
}

.visited-section.open {
    display: flex;
    flex-direction: column;
}

.visited-search {
    display: flex;
    align-items: center;
    gap: 4px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px 8px;
    margin: 4px 0 6px;
}

.visited-search input {
    background: transparent;
    border: none;
    outline: none;
    font-size: 11px;
    flex: 1;
}

.ptcard.visited {
    background: #fafafa;
    border-color: #e2e8f0;
    box-shadow: none !important;
}
</style>

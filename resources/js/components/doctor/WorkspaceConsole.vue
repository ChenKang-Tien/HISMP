<template>
    <div class="lower-section" v-if="store.currentPatient">
        <!-- 頂部頁籤與主按鈕列 -->
        <div class="lower-tabs">
            <div
                class="lower-tab"
                :class="{ active: store.lowerActiveTab === 'tab-order' }"
                @click="store.lowerActiveTab = 'tab-order'"
            >
                <i class="ti ti-prescription"></i> 整合醫囑工作台
            </div>
            <div
                class="lower-tab"
                :class="{ active: store.lowerActiveTab === 'tab-pn' }"
                @click="store.lowerActiveTab = 'tab-pn'"
            >
                <i class="ti ti-notes"></i> Progress Notes
            </div>
            <button
                class="draft-btn"
                style="margin-left: 8px; padding: 4px 12px; font-size: 11px"
            >
                <i class="ti ti-device-floppy"></i> 暫存草稿
            </button>
            <span
                style="
                    font-size: 10px;
                    color: var(--slate-lt);
                    margin-left: 6px;
                "
                >自動暫存：15:24</span
            >
            <div style="flex: 1"></div>
            <button
                class="send-btn"
                style="margin-right: 8px; padding: 5px 14px; font-size: 11px"
                @click="store.openModal('modal-ward-summary')"
            >
                <i class="ti ti-check"></i> ✅ 具名送出
            </button>
        </div>

        <!-- 頁籤內文主體區 -->
        <div class="lower-body">
            <!-- ════ 面板一：醫囑工作台 ════ -->
            <div
                class="lower-panel"
                :class="{ active: store.lowerActiveTab === 'tab-order' }"
            >
                <div class="order-tabs">
                    <button
                        class="order-tab"
                        :class="{ active: store.orderSubTab === 'op1' }"
                        @click="store.orderSubTab = 'op1'"
                    >
                        一｜巡床臨時醫囑
                    </button>
                    <button
                        class="order-tab"
                        :class="{ active: store.orderSubTab === 'op2' }"
                        @click="store.orderSubTab = 'op2'"
                    >
                        二｜透析醫囑參數
                    </button>
                </div>

                <!-- 頁籤一：巡床臨時醫囑 (SOAP) -->
                <div
                    class="order-panel"
                    :class="{ active: store.orderSubTab === 'op1' }"
                >
                    <!-- 工具列 -->
                    <div
                        style="
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            margin-bottom: 8px;
                        "
                    >
                        <button
                            @click="store.openWardLastPreview()"
                            style="
                                font-size: 11px;
                                background: var(--slate-ul);
                                border: 1.5px solid var(--border);
                                border-radius: 6px;
                                padding: 4px 10px;
                                cursor: pointer;
                                color: var(--slate);
                            "
                        >
                            📋 查看上次巡床記錄
                        </button>
                        <span style="font-size: 10px; color: var(--slate-lt)"
                            >{{ store.currentPatient.name }} 床
                            {{ store.currentPatient.bedNo }} 2026-06-06</span
                        >
                    </div>

                    <!-- SOAP 表單網格 -->
                    <div class="soap-grid">
                        <!-- S 欄 -->
                        <div class="soap-cell">
                            <span class="soap-label">S</span>
                            <textarea
                                v-model="store.soapData.s"
                                class="soap-input"
                                placeholder="主訴（選填）..."
                                rows="1"
                            ></textarea>
                        </div>
                        <!-- O 欄 (唯讀系統自動帶入) -->
                        <div class="soap-cell">
                            <span class="soap-label">O</span>
                            <textarea class="soap-o" readonly rows="1">
BP 129/86 · PR 89 · BW 61.8kg · Hb 10.2 · Kt/V 1.15 · URR 68.2%</textarea
                            >
                        </div>
                        <!-- A 欄 -->
                        <div class="soap-cell" style="grid-column: span 2">
                            <span class="soap-label">A</span>
                            <textarea
                                v-model="store.soapData.a"
                                class="soap-input"
                                placeholder="評估（選填）..."
                                rows="1"
                            ></textarea>
                        </div>
                        <!-- P 欄 + 備註並排 -->
                        <div
                            style="
                                display: flex;
                                gap: 8px;
                                align-items: flex-start;
                                grid-column: span 2;
                                justify-content: flex-start;
                                width: 100%;
                            "
                        >
                            <div
                                class="p-cell"
                                style="flex: 0 1 auto; min-width: 0"
                            >
                                <span class="soap-label">P</span>
                                <div class="p-content">
                                    <div class="p-btns">
                                        <button
                                            class="p-btn"
                                            @click="
                                                store.openPPopout('理學檢查')
                                            "
                                        >
                                            🩺 理學檢查
                                        </button>
                                        <button
                                            class="p-btn"
                                            @click="store.openPPopout('檢驗室')"
                                        >
                                            🧪 檢驗室
                                        </button>
                                        <button
                                            class="p-btn has-data"
                                            @click="store.openPPopout('藥物')"
                                        >
                                            💊 藥物 ✓
                                        </button>
                                    </div>
                                    <!-- 執行摘要統計 -->
                                    <div class="exec-summary">
                                        <div class="exec-col">
                                            <div class="exec-col-title">
                                                💊 藥物醫囑
                                            </div>
                                            <div class="exec-item">
                                                Buscopan 1# PO PRN 3天<span
                                                    class="exec-done"
                                                    >✓ 劉護士 14:35</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 備註欄 -->
                            <div
                                class="soap-cell"
                                style="flex: 1; min-width: 150px"
                            >
                                <span class="soap-label note-lbl">備</span>
                                <textarea
                                    v-model="store.soapData.note"
                                    class="soap-input"
                                    placeholder="備註（選填）..."
                                    rows="1"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 今日巡床臨時醫囑記錄看板 -->
                    <div
                        style="
                            margin-top: 10px;
                            border-top: 1px solid var(--border);
                            padding-top: 8px;
                        "
                    >
                        <div
                            style="
                                font-size: 11px;
                                font-weight: 700;
                                color: var(--slate-lt);
                                margin-bottom: 6px;
                            "
                        >
                            今日巡床臨時醫囑記錄
                        </div>
                        <div
                            style="
                                font-size: 12px;
                                background: var(--slate-ul);
                                border-radius: 6px;
                                padding: 7px 10px;
                                margin-bottom: 4px;
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            "
                        >
                            <span>💊 藥物醫囑 Buscopan 1# PO PRN 3天</span>
                            <span
                                style="
                                    font-size: 10px;
                                    color: var(--green);
                                    font-weight: 600;
                                "
                                >✓ 劉護士 14:35</span
                            >
                        </div>
                    </div>
                </div>

                <!-- 頁籤二：透析醫囑參數 (精確還原 V39 長期醫囑全矩陣) -->
                <div
                    class="order-panel"
                    :class="{ active: store.orderSubTab === 'op2' }"
                >
                    <div class="param-overview">
                        <div class="param-overview-title">
                            📋 目前醫囑總覽（點入按鈕修改）
                        </div>

                        <!-- 第一排：透析參數 / 抗凝劑 / 長期用藥 -->
                        <div class="overview-grid">
                            <!-- 1. 透析參數 -->
                            <div class="overview-item">
                                <div class="overview-item-label">
                                    <span class="dx-tag diag">📊 透析參數</span>
                                    <button
                                        class="param-edit-btn"
                                        @click="
                                            store.openModal('modal-param-edit')
                                        "
                                    >
                                        ✏️ 修改
                                    </button>
                                </div>
                                <div
                                    class="overview-item-val"
                                    style="margin-top: 4px"
                                >
                                    <div
                                        v-html="
                                            store.dialysisParamsTrace
                                                .dialyzer ||
                                            `透析器 Dialyzer: ${store.currentPatient.dialyzer || 'FX80 Classix'}`
                                        "
                                    ></div>
                                    <div>
                                        頻率 Frequency: 3 次/週 時數 Duration: 4
                                        h
                                    </div>
                                    <div>
                                        血液流速 BF: 250 ml/min 透析液流速 DF:
                                        500 ml/min
                                    </div>
                                    <div>透析液鈣 Dialysate Ca: 3.0 mEq/L</div>
                                    <div
                                        style="
                                            margin-top: 4px;
                                            display: flex;
                                            align-items: center;
                                            gap: 6px;
                                        "
                                    >
                                        <label class="fs-toggle"
                                            ><input
                                                type="checkbox"
                                                checked /><span
                                                class="fs-slider"
                                            ></span
                                        ></label>
                                        <span
                                            style="
                                                font-size: 11px;
                                                color: var(--slate);
                                            "
                                            >透前血糖 F/S Blood Glucose</span
                                        >
                                    </div>
                                </div>
                                <div style="margin-top: 6px; text-align: right">
                                    <span
                                        @click="
                                            store.openExecRecord('透析參數')
                                        "
                                        class="trace-link"
                                        >✅ 已執行變動記錄</span
                                    >
                                </div>
                            </div>

                            <!-- 2. 抗凝劑 (HEPARIN) -->
                            <div class="overview-item">
                                <div class="overview-item-label">
                                    <span class="dx-tag anticoag-tag"
                                        >💉 HEPARIN</span
                                    >
                                    <button
                                        class="param-edit-btn"
                                        @click="
                                            store.openModal(
                                                'modal-heparin-edit',
                                            )
                                        "
                                    >
                                        ✏️ 修改
                                    </button>
                                </div>
                                <div
                                    class="overview-item-val"
                                    style="margin-top: 4px; line-height: 1.9"
                                >
                                    <div
                                        style="
                                            color: var(--slate-lt);
                                            font-size: 10px;
                                            font-weight: 600;
                                        "
                                    >
                                        抗凝劑
                                    </div>
                                    <div style="font-weight: 600">
                                        {{
                                            store.currentPatient.anticoagulant
                                                ?.type || "Heparin"
                                        }}
                                    </div>
                                    <div>
                                        Initial：{{
                                            store.currentPatient.anticoagulant
                                                ?.initial || "1500"
                                        }}
                                        U
                                    </div>
                                    <div>
                                        Maintain：{{
                                            store.currentPatient.anticoagulant
                                                ?.maintain || "300"
                                        }}
                                        U/hr
                                    </div>
                                </div>
                                <div style="margin-top: 6px; text-align: right">
                                    <span
                                        @click="store.openExecRecord('抗凝劑')"
                                        class="trace-link"
                                        >✅ 已執行變動記錄</span
                                    >
                                </div>
                            </div>

                            <!-- 3. 長期用藥 -->
                            <div class="overview-item">
                                <div class="overview-item-label">
                                    <span class="dx-tag long-drug-tag"
                                        >💊 長期用藥</span
                                    >
                                    <button
                                        class="param-edit-btn"
                                        @click="
                                            store.openModal(
                                                'modal-drug-long-edit',
                                            )
                                        "
                                    >
                                        ✏️ 修改
                                    </button>
                                </div>
                                <div
                                    class="overview-item-val"
                                    style="margin-top: 4px"
                                >
                                    <div
                                        v-for="(drug, idx) in store
                                            .currentPatient.longTermDrugs || []"
                                        :key="idx"
                                    >
                                        <div v-if="drug.deleted">
                                            <span
                                                style="
                                                    text-decoration: line-through;
                                                    color: var(--slate-lt);
                                                "
                                                >{{ drug.name }}</span
                                            >
                                            <small
                                                style="
                                                    color: var(--red);
                                                    display: block;
                                                "
                                                >{{ drug.deleteTrace }}</small
                                            >
                                        </div>
                                        <div
                                            v-else
                                            style="
                                                display: flex;
                                                align-items: center;
                                                justify-content: space-between;
                                            "
                                        >
                                            <span>{{ drug.name }}</span>
                                        </div>
                                    </div>
                                    <div
                                        v-if="
                                            !store.currentPatient.longTermDrugs
                                                ?.length
                                        "
                                        style="
                                            color: var(--slate-lt);
                                            font-style: italic;
                                        "
                                    >
                                        目前無紀錄
                                    </div>
                                </div>
                                <div style="margin-top: 6px; text-align: right">
                                    <span
                                        @click="
                                            store.openExecRecord('長期用藥')
                                        "
                                        class="trace-link"
                                        style="color: var(--amber)"
                                        >🟡 已執行變動記錄</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- 第二排：🔴EPO / 🟢IRON / 💧靜脈給藥 -->
                        <div class="overview-grid" style="margin-top: 6px">
                            <!-- 4. EPO 施打矩陣 -->
                            <div class="overview-item">
                                <div class="overview-item-label">
                                    <span class="dx-tag epo-tag">🔴 EPO</span>
                                    <button
                                        class="param-edit-btn"
                                        @click="
                                            store.openModal('modal-epo-matrix')
                                        "
                                    >
                                        ✏️ 修改
                                    </button>
                                </div>
                                <div style="margin-top: 5px; overflow-x: auto">
                                    <table class="matrix-mini-table">
                                        <thead>
                                            <tr>
                                                <th>HCT</th>
                                                <th>W12</th>
                                                <th>W34</th>
                                                <th>W56</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>&lt; 30</td>
                                                <td class="active-cell">N20</td>
                                                <td>—</td>
                                                <td>—</td>
                                            </tr>
                                            <tr>
                                                <td>30~35</td>
                                                <td class="active-cell">N40</td>
                                                <td class="active-cell">N40</td>
                                                <td>—</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="margin-top: 6px; text-align: right">
                                    <span
                                        @click="store.openExecRecord('EPO')"
                                        class="trace-link"
                                        style="color: var(--amber)"
                                        >🟡 已執行 1/3</span
                                    >
                                </div>
                            </div>

                            <!-- 5. IRON 施打矩陣 -->
                            <div class="overview-item">
                                <div class="overview-item-label">
                                    <span class="dx-tag iron-tag">🟢 IRON</span>
                                    <button
                                        class="param-edit-btn"
                                        @click="
                                            store.openModal('modal-iron-matrix')
                                        "
                                    >
                                        ✏️ 修改
                                    </button>
                                </div>
                                <div style="margin-top: 5px; overflow-x: auto">
                                    <table class="matrix-mini-table iron-mini">
                                        <thead>
                                            <tr>
                                                <th>Ferritin</th>
                                                <th>藥名</th>
                                                <th>量</th>
                                                <th>頻率</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>&lt; 301</td>
                                                <td class="active-cell">
                                                    Venofer
                                                </td>
                                                <td>1pc</td>
                                                <td>QW</td>
                                            </tr>
                                            <tr>
                                                <td>301~401</td>
                                                <td class="active-cell">
                                                    Venofer
                                                </td>
                                                <td>1pc</td>
                                                <td>QOW</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="margin-top: 6px; text-align: right">
                                    <span
                                        @click="store.openExecRecord('IRON')"
                                        class="trace-link"
                                        style="color: var(--slate-lt)"
                                        >⬜ 待執行</span
                                    >
                                </div>
                            </div>

                            <!-- 6. 靜脈給藥 -->
                            <div class="overview-item">
                                <div class="overview-item-label">
                                    <span class="dx-tag iv-tag"
                                        >💧 靜脈給藥</span
                                    >
                                    <button
                                        class="param-edit-btn"
                                        @click="
                                            store.openModal('modal-iv-edit')
                                        "
                                    >
                                        ✏️ 修改
                                    </button>
                                </div>
                                <div
                                    class="overview-item-val"
                                    style="margin-top: 4px"
                                >
                                    <div
                                        style="
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                        "
                                    >
                                        <span
                                            >Recormon 2000 IU IV Post HD
                                            BIW</span
                                        >
                                    </div>
                                </div>
                                <div style="margin-top: 6px; text-align: right">
                                    <span
                                        @click="
                                            store.openExecRecord('靜脈給藥')
                                        "
                                        class="trace-link"
                                        >✅ 已執行 3/3</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ 面板二：Progress Notes ════ -->
            <div
                class="lower-panel"
                :class="{ active: store.lowerActiveTab === 'tab-pn' }"
            >
                <div style="margin-bottom: 8px">
                    <button @click="store.openPNEdit('new')" class="add-pn-btn">
                        ＋ 新增 Progress Notes
                    </button>
                </div>
                <!-- 歷史病程筆記列表 -->
                <div
                    v-for="note in store.currentPatient.progressNotes || []"
                    :key="note.id"
                    class="pn-card"
                    :class="{ 'pn-locked': note.locked }"
                >
                    <div class="pn-hdr">
                        <span class="pn-date">{{ note.date }}</span>
                        <span class="pn-doctor">{{ note.doctor }}</span>
                        <i
                            v-if="!note.locked"
                            class="ti ti-pencil pn-edit"
                            @click="store.openPNEdit('edit', note)"
                        ></i>
                        <span
                            v-else
                            style="
                                margin-left: auto;
                                font-size: 11px;
                                color: var(--slate-lt);
                            "
                            >🔒 已上鎖</span
                        >
                    </div>
                    <div class="pn-body">{{ note.content }}</div>
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
/* 100% 貼合 V39 醫學風格樣式 */
.lower-section {
    flex: 1;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    background: white;
    overflow: hidden;
}
.lower-tabs {
    display: flex;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
    align-items: center;
    background: white;
}
.lower-tab {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: var(--slate-lt);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: all 0.15s;
    white-space: nowrap;
}
.lower-tab.active {
    color: var(--pri);
    border-bottom-color: var(--pri);
}

.draft-btn {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    color: var(--slate);
    border-radius: 6px;
    padding: 5px 12px;
    font-size: 11px;
    cursor: pointer;
}
.send-btn {
    background: var(--green);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 18px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-left: auto;
}

.lower-body {
    flex: 1;
    overflow: hidden;
    padding: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
}
.lower-panel {
    display: none;
}
.lower-panel.active {
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
    padding: 8px 12px;
}

.order-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 8px;
    flex-shrink: 0;
}
.order-tab {
    background: var(--slate-ul);
    border: 1.5px solid var(--border);
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--slate);
    cursor: pointer;
}
.order-tab.active {
    background: var(--pri-lt);
    border-color: var(--pri);
    color: var(--pri);
}
.order-panel {
    display: none;
}
.order-panel.active {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
    overflow-y: auto;
}

/* SOAP 排版 */
.soap-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 6px;
}
.soap-cell {
    display: flex;
    gap: 5px;
    align-items: flex-start;
}
.soap-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--pri);
    background: var(--pri-lt);
    border: 1px solid var(--pri-bd);
    border-radius: 5px;
    padding: 4px 8px;
    flex-shrink: 0;
    min-width: 30px;
    text-align: center;
}
.soap-label.note-lbl {
    background: var(--slate-ul);
    border-color: var(--border);
    color: var(--slate);
}

.soap-input {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 5px 7px;
    font-size: 12px;
    resize: none;
    min-height: 34px;
    outline: none;
    transition: border-color 0.15s;
    line-height: 1.5;
}
.soap-input:focus {
    border-color: var(--pri);
}
.soap-o {
    flex: 1;
    border: 1px solid var(--pri-bd);
    border-radius: 6px;
    padding: 5px 7px;
    font-size: 11px;
    resize: none;
    min-height: 34px;
    outline: none;
    background: var(--pri-lt);
    color: #334155;
    line-height: 1.6;
}

.p-cell {
    display: flex;
    gap: 5px;
    align-items: flex-start;
    grid-column: 1/-1;
}
.p-content {
    flex: 1;
}
.p-btns {
    display: flex;
    gap: 5px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.p-btn {
    background: white;
    border: 1.5px solid var(--border);
    border-radius: 6px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 600;
    color: var(--slate);
    cursor: pointer;
    transition: all 0.15s;
}
.p-btn:hover {
    border-color: var(--pri);
    color: var(--pri);
}
.p-btn.has-data {
    border-color: var(--green-bd);
    background: var(--green-lt);
    color: var(--green);
}

.exec-summary {
    display: flex;
    gap: 6px;
}
.exec-col {
    flex: 1;
    background: var(--slate-ul);
    border: 1px solid var(--border);
    border-radius: 7px;
    padding: 8px 10px;
    min-width: 0;
}
.exec-col-title {
    font-size: 10px;
    font-weight: 700;
    color: var(--slate);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 5px;
}
.exec-item {
    font-size: 12px;
    color: #1e293b;
    padding: 3px 0;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    line-height: 1.5;
    justify-content: space-between;
}
.exec-done {
    color: var(--green);
    font-size: 11px;
    white-space: nowrap;
    font-weight: 600;
}

/* 長期醫囑參數總覽排版 */
.param-overview {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 6px 10px;
    overflow-y: auto;
    flex: 1;
}
.param-overview-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--slate);
    margin-bottom: 5px;
}
.overview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 6px;
}
.overview-item {
    background: white;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    padding: 6px 10px;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.overview-item-label {
    font-size: 9px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.overview-item-val {
    font-size: 11px;
    color: #1e293b;
    line-height: 1.5;
    flex: 1;
}
.param-edit-btn {
    font-size: 11px;
    color: var(--pri);
    cursor: pointer;
    background: white;
    border: 1px solid var(--pri-bd);
    border-radius: 4px;
    padding: 1px 7px;
}

/* 標籤小章配色 */
.dx-tag {
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 10px;
    font-weight: 700;
}
.dx-tag.diag {
    background: var(--pri-lt);
    color: var(--pri);
}
.anticoag-tag {
    background: var(--purple-lt);
    color: var(--purple);
    border: 1px solid var(--purple-bd);
}
.long-drug-tag {
    background: #fff7ed;
    color: #ea580c;
    border: 1px solid #fed7aa;
}
.epo-tag {
    background: var(--red-lt);
    color: var(--red);
    border: 1px solid var(--red-bd);
}
.iron-tag {
    background: var(--green-lt);
    color: var(--green);
    border: 1px solid var(--green-bd);
}
.iv-tag {
    background: #e0f2fe;
    color: #0891b2;
    border: 1px solid #bae6fd;
}

.trace-link {
    font-size: 11px;
    color: var(--green);
    cursor: pointer;
    font-weight: 600;
    text-decoration: underline dotted;
}
.trace-link:hover {
    color: var(--pri);
}

/* 微型矩陣表格 (EPO / IRON 預覽) */
.matrix-mini-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    margin-top: 3px;
}
.matrix-mini-table th {
    background: var(--pri);
    color: white;
    padding: 2px 4px;
    font-size: 9px;
}
.matrix-mini-table.iron-mini th {
    background: var(--green);
}
.matrix-mini-table td {
    padding: 2px 4px;
    border: 1px solid var(--border);
    text-align: center;
    background: white;
}
.matrix-mini-table td.active-cell {
    color: var(--pri);
    font-weight: 700;
}
.matrix-mini-table.iron-mini td.active-cell {
    color: var(--green);
}

/* F/S 切換開關 */
.fs-toggle {
    position: relative;
    width: 32px;
    height: 18px;
    cursor: pointer;
}
.fs-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}
.fs-slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 10px;
    transition: 0.2s;
}
.fs-slider:before {
    content: "";
    position: absolute;
    width: 14px;
    height: 14px;
    left: 2px;
    bottom: 2px;
    background: white;
    border-radius: 50%;
    transition: 0.2s;
}
.fs-toggle input:checked + .fs-slider {
    background: var(--pri);
}
.fs-toggle input:checked + .fs-slider:before {
    transform: translateX(14px);
}

/* Progress Notes 卡片 */
.add-pn-btn {
    width: 100%;
    padding: 8px 12px;
    background: var(--pri-lt);
    border: 1.5px dashed var(--pri-bd);
    border-radius: 8px;
    color: var(--pri);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.add-pn-btn:hover {
    background: #dbeafe;
}
.pn-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 8px 10px;
    margin-bottom: 6px;
    background: white;
}
.pn-hdr {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}
.pn-date {
    font-size: 11px;
    font-weight: 700;
    color: var(--pri);
}
.pn-doctor {
    font-size: 10px;
    color: var(--slate-lt);
}
.pn-edit {
    margin-left: auto;
    color: var(--slate-lt);
    cursor: pointer;
    font-size: 14px;
}
.pn-edit:hover {
    color: var(--pri);
}
.pn-body {
    font-size: 12px;
    color: #334155;
    line-height: 1.6;
}
.pn-locked {
    background: var(--slate-ul);
}
</style>

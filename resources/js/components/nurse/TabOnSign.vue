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
                <div class="vsign-v">
                    {{ store.vsignData.sys || "0" }}/{{
                        store.vsignData.dia || "0"
                    }}
                </div>
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
                <i class="ti ti-pill"></i>透前給藥（透析用藥）
            </div>
            <button class="add-drug-btn" @click="showDrugModal = true">
                ➕ 新增處置藥物
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
            <!-- 主責護理師 On-Sign -->
            <div
                :class="['sbtn', { signed: store.mainSigned }]"
                @click="executeMainOnSign"
                :title="store.mainSigned ? '點擊解除主簽章' : '主責護理師點擊進行 On-Sign 核簽'"
            >
                {{
                    store.mainSigned
                        ? "✓ " + (store.mainNurseName || "主責") + " On-Sign 已簽"
                        : "主責護理師 On-Sign"
                }}
            </div>

            <!-- 雙簽護理師 Double Sign (必須不同護理師) -->
            <div
                :class="['sbtn', { signed: store.doubleSigned }]"
                :style="!store.mainSigned ? 'opacity:0.6; cursor:not-allowed;' : ''"
                @click="triggerDoubleSign"
                :title="
                    !store.mainSigned
                        ? '需先完成主責護理師 On-Sign 後方可進行 Double Sign'
                        : (store.doubleSigned
                            ? '點擊解除雙簽'
                            : '第二位護理師臨櫃覆核 Double Sign')
                "
            >
                {{
                    store.doubleSigned
                        ? "✓ " + (store.doubleNurseName || "覆核") + " Double Sign 已雙簽"
                        : "雙簽護理師 Double Sign"
                }}
            </div>
        </div>

        <!-- ═══════════════════════════════════ -->
        <!-- 🎛️ MODALS 彈窗完全還原（前置整備、三大評估統一、生命徵象） -->
        <!-- ═══════════════════════════════════ -->

        <!-- ① 前置整備彈窗 (100% 傳承體重彈窗風格) -->
        <div :class="['modal-overlay', { open: showPrepModal }]" @click.self="showPrepModal = false">
            <div class="modal-container-vsign" style="max-width: 480px;">
                <div class="vsign-modal-hdr">
                    <div class="vsign-hdr-left">
                        <span class="vsign-hdr-title">
                            <i class="ti ti-checklist"></i> 前置整備確認（依醫囑自動產生）
                        </span>
                        <span v-if="store.currentPatient" class="vsign-pt-badge">
                            {{ store.currentPatient.bed || "—" }}床 | {{ store.currentPatient.name }}
                        </span>
                    </div>
                    <button class="vsign-close-x" @click="showPrepModal = false" title="關閉">
                        ×
                    </button>
                </div>

                <div class="vsign-modal-body">
                    <div class="vsign-meta-desc">
                        <span class="meta-icon">💡</span>
                        <div>
                            對照實物逐項確認就位，點擊項目勾選完成核對。
                        </div>
                    </div>

                    <div class="vsign-sec-card">
                        <div
                            style="
                                font-size: 11px;
                                font-weight: 700;
                                color: #475569;
                                margin-bottom: 5px;
                                text-transform: uppercase;
                                letter-spacing: 0.4px;
                            "
                        >
                            📦 醫器設備
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
                                font-size: 11px;
                                font-weight: 700;
                                color: #475569;
                                margin: 10px 0 5px;
                                text-transform: uppercase;
                                letter-spacing: 0.4px;
                            "
                        >
                            💊 藥材耗材
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
                                font-size: 11.5px;
                                color: #b45309;
                                margin-top: 8px;
                            "
                        >
                            ⚠️ IRON 本月未到施打週，無需準備
                        </div>
                    </div>

                    <div class="mbtn-row" style="margin-top: 14px; margin-bottom: 0;">
                        <button class="mbtn sec" @click="showPrepModal = false">
                            關閉
                        </button>
                        <button class="mbtn pri" @click="showPrepModal = false">
                            ✅ 全部確認就位
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ③ 透前給藥彈窗 (100% 傳承體重彈窗風格) -->
        <div :class="['modal-overlay', { open: showDrugModal }]" @click.self="showDrugModal = false">
            <div class="modal-container-vsign" style="max-width: 440px">
                <div class="vsign-modal-hdr">
                    <div class="vsign-hdr-left">
                        <span class="vsign-hdr-title">
                            <i class="ti ti-pill"></i> 新增透前給藥
                        </span>
                        <span v-if="store.currentPatient" class="vsign-pt-badge">
                            {{ store.currentPatient.bed || "—" }}床 | {{ store.currentPatient.name }}
                        </span>
                    </div>
                    <button class="vsign-close-x" @click="showDrugModal = false" title="關閉">
                        ×
                    </button>
                </div>

                <div class="vsign-modal-body">
                    <div class="vsign-sec-card">
                        <div class="form-label" style="font-weight: 700; color: #475569;">藥品名稱 (搜尋)</div>
                        <input
                            type="text"
                            class="form-input vsign-styled-input"
                            placeholder="輸入藥名搜尋..."
                            v-model="newDrugForm.medicine"
                            @input="searchMedicines($event.target.value)"
                        />
                        <div v-if="medicineOptions.length > 0" style="position: absolute; width: 360px; z-index: 999; border: 1.5px solid #99f6e4; border-radius: 6px; max-height: 150px; overflow-y: auto; background: #fff; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);">
                            <div v-for="m in medicineOptions" :key="m.id" @click="selectMedicine(m)" style="padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.backgroundColor='#f0fdfa'" onmouseout="this.style.backgroundColor='transparent'">
                                {{ m.name }}
                            </div>
                        </div>

                        <div class="form-label" style="font-weight: 700; color: #475569; margin-top: 8px;">用量</div>
                        <input v-model.number="newDrugForm.amount" type="number" class="form-input vsign-styled-input" />

                        <div class="form-label" style="font-weight: 700; color: #475569; margin-top: 8px;">給藥途徑</div>
                        <select v-model="newDrugForm.route_id" class="form-input vsign-styled-input">
                            <option v-for="r in routeOptions" :key="r.id" :value="r.id">
                                {{ r.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mbtn-row" style="margin-top: 14px; margin-bottom: 0;">
                        <button class="mbtn sec" @click="showDrugModal = false">取消</button>
                        <button class="mbtn pri" @click="saveNewDrug">✅ 確認新增給藥</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ② 三大評估統一式大彈窗 (100% 傳承體重彈窗風格) -->
        <div :class="['modal-overlay', { open: showAssessModal }]" @click.self="showAssessModal = false">
            <div class="modal-container-vsign" style="max-width: 540px;">
                <!-- 頂部 Header -->
                <div class="vsign-modal-hdr">
                    <div class="vsign-hdr-left">
                        <span class="vsign-hdr-title">
                            <i class="ti ti-stethoscope"></i> 透析前三大臨床評估
                        </span>
                        <span v-if="store.currentPatient" class="vsign-pt-badge">
                            床號: {{ store.currentPatient.bed || "—" }} | {{ store.currentPatient.name || "未選病患" }}
                        </span>
                    </div>
                    <button class="vsign-close-x" @click="showAssessModal = false" title="關閉">
                        ×
                    </button>
                </div>

                <!-- 彈窗內容 Body -->
                <div class="vsign-modal-body">
                    <!-- 提示條 -->
                    <div class="vsign-meta-desc">
                        <span class="meta-icon">💡</span>
                        <div>
                            <b>固定位置 CSV 編碼存證</b>：請逐項切換頁籤確認血管通路、意識與皮膚狀態，資料將自動編碼存入資料庫以供臨床查核。
                        </div>
                    </div>

                    <!-- 三分頁切換膠囊按鈕 (體重彈窗風格) -->
                    <div class="cat-pill-switch" style="display: flex; gap: 4px; margin-bottom: 12px;">
                        <button
                            type="button"
                            :class="[
                                'assess-tab-btn',
                                { active: assessActiveTab === 0 },
                            ]"
                            @click="assessActiveTab = 0"
                        >
                            🌿 血管通路
                        </button>
                        <button
                            type="button"
                            :class="[
                                'assess-tab-btn',
                                { active: assessActiveTab === 1 },
                            ]"
                            @click="assessActiveTab = 1"
                        >
                            🧠 意識狀態
                        </button>
                        <button
                            type="button"
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
                    <div v-show="assessActiveTab === 0" class="vsign-sec-card">
                        <div class="vsign-sec-lbl">
                            <i class="ti ti-dna"></i> 血管通路評估
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">種類</div>
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
                            <div class="form-label" style="font-weight: 700; color: #475569;">Permcath 長度</div>
                            <input
                                v-model="assessForm.permcathLen"
                                class="form-input vsign-styled-input"
                                type="text"
                                placeholder="如：15.5 cm"
                                style="margin-bottom: 0"
                            />
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">位置 (L/R)</div>
                        <div class="assess-opt-row">
                            <span
                                v-for="p in ['L\'t', 'R\'t']"
                                :key="p"
                                :class="[
                                    'assess-opt',
                                    { sel: assessForm.vascularPosLR === p },
                                ]"
                                @click="assessForm.vascularPosLR = p"
                            >
                                {{ p }}
                            </span>
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">位置 (中心/導管)</div>
                        <div class="assess-opt-row">
                            <span
                                v-for="p in ['SCVC', 'JVC', 'FVC']"
                                :key="p"
                                :class="[
                                    'assess-opt',
                                    { sel: assessForm.vascularPosCenter === p },
                                ]"
                                @click="assessForm.vascularPosCenter = p"
                            >
                                {{ p }}
                            </span>
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">評估 (可多選)</div>
                        <div class="assess-opt-row">
                            <span
                                v-for="(e, index) in evalOptions"
                                :key="e.val"
                                :class="[
                                    'assess-opt',
                                    {
                                        sel: assessForm.vascularEval[index] === '1',
                                    },
                                ]"
                                @click="toggleVascularEval(index)"
                            >
                                {{ e.label }}
                            </span>
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">備註</div>
                        <textarea
                            v-model="assessForm.vascularNote"
                            class="form-input vsign-styled-input"
                            rows="2"
                            placeholder="填寫血管通路備註..."
                            style="margin-bottom: 0;"
                        ></textarea>
                    </div>

                    <!-- 頁籤1：意識狀態 -->
                    <div v-show="assessActiveTab === 1" class="vsign-sec-card">
                        <div class="vsign-sec-lbl">
                            <i class="ti ti-brain"></i> 意識狀態評估
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">意識狀態</div>
                        <div class="assess-opt-row">
                            <span
                                v-for="c in consciousOptions"
                                :key="c.val"
                                :class="[
                                    'assess-opt',
                                    { sel: assessForm.consciousState === c.label },
                                ]"
                                @click="assessForm.consciousState = c.label"
                            >
                                {{ c.label }}
                            </span>
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">備註</div>
                        <textarea
                            v-model="assessForm.consciousNote"
                            class="form-input vsign-styled-input"
                            rows="2"
                            placeholder="填寫意識狀態備註..."
                            style="margin-bottom: 0;"
                        ></textarea>
                    </div>

                    <!-- 頁籤2：皮膚完整性 -->
                    <div v-show="assessActiveTab === 2" class="vsign-sec-card">
                        <div class="vsign-sec-lbl">
                            <i class="ti ti-bandage"></i> 皮膚完整性評估
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569;">皮膚狀態</div>
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
                            <div class="form-col" style="flex: 1">
                                <div class="form-label" style="font-weight: 700; color: #475569;">部位</div>
                                <input
                                    v-model="assessForm.skinLocation"
                                    class="form-input vsign-styled-input"
                                    placeholder="輸入部位..."
                                    style="margin-bottom: 0"
                                />
                            </div>
                            <div class="form-col" style="flex: 1">
                                <div class="form-label" style="font-weight: 700; color: #475569;">大小（cm）</div>
                                <input
                                    v-model="assessForm.skinSize"
                                    class="form-input vsign-styled-input"
                                    placeholder="輸入大小..."
                                    style="margin-bottom: 0"
                                />
                            </div>
                        </div>
                        <div class="form-label" style="font-weight: 700; color: #475569; margin-top: 8px;">備註</div>
                        <textarea
                            v-model="assessForm.skinNote"
                            class="form-input vsign-styled-input"
                            rows="2"
                            placeholder="填寫皮膚狀態備註..."
                            style="margin-bottom: 0;"
                        ></textarea>
                    </div>

                    <!-- 底部按鈕列 -->
                    <div class="mbtn-row" style="margin-top: 14px; margin-bottom: 0;">
                        <button class="mbtn sec" @click="showAssessModal = false">
                            取消
                        </button>
                        <button class="mbtn pri" @click="saveUnifiedAssess">
                            ✅ 儲存三大評估
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ③ 生命徵象手動填寫彈窗 (五大生理參數 - 採用與體重彈窗一致的設計語言) -->
        <div :class="['modal-overlay', { open: showVsignModal }]" @click.self="showVsignModal = false">
            <div class="modal-container-vsign">
                <!-- 頂部 Header -->
                <div class="vsign-modal-hdr">
                    <div class="vsign-hdr-left">
                        <span class="vsign-hdr-title">
                            <i class="ti ti-activity"></i> On-Sign 五大生命徵象輸入
                        </span>
                        <span v-if="store.currentPatient" class="vsign-pt-badge">
                            床號: {{ store.currentPatient.bed || "—" }} | {{ store.currentPatient.name || "未選病患" }}
                        </span>
                    </div>
                    <button class="vsign-close-x" @click="showVsignModal = false" title="關閉">
                        ×
                    </button>
                </div>

                <!-- 彈窗內容 Body -->
                <div class="vsign-modal-body">
                    <!-- 提示條 -->
                    <div class="vsign-meta-desc">
                        <span class="meta-icon">💡</span>
                        <div>
                            <b>臨床數值防呆錄入</b>：血壓請分別輸入收縮壓與舒張壓，有血糖醫囑時請核實輸入 F/S 數值。
                        </div>
                    </div>

                    <!-- 區塊一：血壓數值 (Sys / Dia) -->
                    <div class="vsign-sec-card">
                        <div class="vsign-sec-lbl">
                            <i class="ti ti-heart-rate-monitor"></i> 血壓量測 BP (mmHg)
                        </div>
                        <div class="form-row">
                            <div class="form-col" style="flex: 1">
                                <div class="form-label" style="color: #0f766e; font-weight: 700;">收縮壓 Sys</div>
                                <input
                                    v-model="vsignForm.sys"
                                    class="form-input vsign-styled-input"
                                    placeholder="如 120"
                                    style="margin-bottom: 0"
                                />
                            </div>
                            <div class="form-col" style="flex: 1">
                                <div class="form-label" style="color: #0f766e; font-weight: 700;">舒張壓 Dia</div>
                                <input
                                    v-model="vsignForm.dia"
                                    class="form-input vsign-styled-input"
                                    placeholder="如 80"
                                    style="margin-bottom: 0"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- 區塊二：脈搏、呼吸、體溫與血糖 -->
                    <div class="vsign-sec-card" style="margin-top: 10px;">
                        <div class="vsign-sec-lbl">
                            <i class="ti ti-stethoscope"></i> 其他生理指標
                        </div>
                        <div class="form-row">
                            <div class="form-col" style="flex: 1">
                                <div class="form-label">脈搏 Pulse (次/min)</div>
                                <input
                                    v-model="vsignForm.pr"
                                    class="form-input vsign-styled-input"
                                    placeholder="如 76"
                                    style="margin-bottom: 0"
                                />
                            </div>
                            <div class="form-col" style="flex: 1">
                                <div class="form-label">呼吸 Resp. (次/min)</div>
                                <input
                                    v-model="vsignForm.rr"
                                    class="form-input vsign-styled-input"
                                    placeholder="如 18"
                                    style="margin-bottom: 0"
                                />
                            </div>
                        </div>
                        <div class="form-row" style="margin-top: 8px">
                            <div class="form-col" style="flex: 1">
                                <div class="form-label">體溫 Temp. (°C)</div>
                                <input
                                    v-model="vsignForm.temp"
                                    class="form-input vsign-styled-input"
                                    placeholder="如 36.5"
                                    style="margin-bottom: 0"
                                />
                            </div>
                            <div
                                v-if="store.currentPatient.hasFSOrder"
                                class="form-col"
                                style="flex: 1"
                            >
                                <div class="form-label" style="color: #d97706; font-weight: 700;">血糖 F/S (mg/dL) *</div>
                                <input
                                    v-model="vsignForm.fs"
                                    class="form-input vsign-styled-input"
                                    placeholder="如 100"
                                    style="margin-bottom: 0; border-color: #fcd34d; background: #fffdf5;"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- 底部按鈕列 -->
                    <div class="mbtn-row" style="margin-top: 14px; margin-bottom: 0;">
                        <button class="mbtn sec" @click="showVsignModal = false">
                            取消
                        </button>
                        <button class="mbtn pri" @click="saveVsignForm">
                            ✅ 確認儲存數值
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();

// 模態視窗與分頁指標
const showPrepModal = ref(false);
const showAssessModal = ref(false);
const showVsignModal = ref(false);
const assessActiveTab = ref(0);
const isDoubleSigned = ref(false); // 相容歷史引用

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
const vsignForm = ref({ sys: "", dia: "", pr: "", rr: "", temp: "", fs: "" });

// 三大評估暫存表單物件
const assessForm = ref({
    vascularKind: "AVF",
    vascularPosLR: "L't",
    vascularPosCenter: "SCVC",
    permcathLen: "",
    vascularEval: ["正常"],
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
    // 檢查 store.vsignData.sys/dia 是否存在
    vsignForm.value = {
        sys: store.vsignData.sys || "",
        dia: store.vsignData.dia || "",
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
const saveVsignForm = async () => {
    // 儲存至後端
    try {
        await store.updateVitals(store.currentPatient.id, {
            sys: vsignForm.value.sys,
            dia: vsignForm.value.dia,
            pr: vsignForm.value.pr,
            rr: vsignForm.value.rr,
            temp: vsignForm.value.temp,
            fs: vsignForm.value.fs,
        });
        alert("✅ 生命徵象已成功送出！");

        // 更新 UI State
        let tempVal = vsignForm.value.temp;
        if (tempVal && !tempVal.includes("°C")) tempVal = tempVal + "°C";

        store.vsignData = {
            sys: vsignForm.value.sys || "",
            dia: vsignForm.value.dia || "",
            pr: vsignForm.value.pr || "—",
            rr: vsignForm.value.rr || "—",
            temp: tempVal || "—",
            fs: vsignForm.value.fs || "—",
        };
        store.vsignFilled = true;
        showVsignModal.value = false;
    } catch (e) {
        console.error(
            "🔴 [UpdateVitals API Error]:",
            e.response ? e.response.data : e.message,
        );
        alert("⚠️ 送出失敗，請確認資料格式 (Console 已顯示詳細錯誤)。");
    }
};

// 開啟三大評估
const openAssessModal = async (tabIndex) => {
    try {
        const res = await store.api.get(
            `/dialysis-checks/${store.currentPatient.id}`,
        );
        const assessData = res.data.assess;
        console.log(assessData);

        if (assessData && Object.keys(assessData).length > 0) {
            assessForm.value.vascularKind = assessData.vascular_kind || "AVF";
            assessForm.value.permcathLen = assessData.permcath_len || "";
            assessForm.value.vascularPosLR =
                assessData.vascular_pos_lr || "L't";
            assessForm.value.vascularPosCenter =
                assessData.vascular_pos_center || "SCVC";

            // 處理多選：從陣列轉換 (或者從逗號分隔字串轉陣列)
            if (Array.isArray(assessData.vascular_eval)) {
                // 如果是陣列，假設裡面的元素直接是索引字串
                assessForm.value.vascularEval =
                    assessData.vascular_eval.map(String);
            } else if (typeof assessData.vascular_eval === "string") {
                // 如果是逗號分隔字串 "1,0,0,0,0,0,備註"
                const parts = assessData.vascular_eval.split(",");
                // 前 6 個是位元陣列
                const bits = parts.slice(0, 6);
                const note = parts.slice(6).join(",");

                assessForm.value.vascularEval =
                    bits.length === 6 ? bits : ["1", "0", "0", "0", "0", "0"];
                assessForm.value.vascularNote = note || "";
            } else {
                assessForm.value.vascularEval = ["1", "0", "0", "0", "0", "0"];
                assessForm.value.vascularNote = "";
            }

            console.log(assessForm.value.vascularEval);

            assessForm.value.vascularNote = assessData.vascular_note || "";
            console.log(assessData.conscious);

            const cVal = assessData.conscious || "0";
            console.log("DEBUG: Conscious val from assessData:", cVal);

            // 修正：consciousOptions 裡面的 val 是字串或數字，統一轉為字串比較
            const foundConscious = consciousOptions.find(
                (o) => String(o.val) === String(cVal),
            );
            console.log("foundConscious:" + foundConscious);

            assessForm.value.consciousState = foundConscious
                ? foundConscious.label
                : "清醒";
            console.log(
                "DEBUG: Resolved consciousState:",
                assessForm.value.consciousState,
            );
            assessForm.value.skinState =
                assessData.skin == "1" ? "有傷口(含水泡)" : "完整";
            assessForm.value.consciousNote = assessData.conscious_note || "";
            assessForm.value.skinNote = assessData.skin_note || "";
            assessForm.value.skinLocation = assessData.skin_location || "";
            assessForm.value.skinSize = assessData.skin_size || "";

            // 同步更新 store.assessState
            // store.assessState = {
            //     vascular: `${assessForm.value.vascularKind} ${assessForm.value.vascularPosLR}${assessForm.value.vascularPosCenter} ${
            //         assessForm.value.vascularEval[0] === "1"
            //             ? "正常"
            //             : evalOptions
            //                   .filter(
            //                       (_, idx) =>
            //                           assessForm.value.vascularEval[idx] ===
            //                           "1",
            //                   )
            //                   .map((o) => o.label)
            //                   .join(",")
            //     }${assessForm.value.vascularNote ? " (" + assessForm.value.vascularNote + ")" : ""}`,
            //     conscious:
            //         assessForm.value.consciousState +
            //         (assessForm.value.consciousNote
            //             ? `(${assessForm.value.consciousNote})`
            //             : ""),
            //     skin:
            //         assessForm.value.skinState +
            //         (assessForm.value.skinLocation || assessForm.value.skinSize
            //             ? `(${assessForm.value.skinLocation}${assessForm.value.skinSize ? " " + assessForm.value.skinSize + "cm" : ""})`
            //             : "") +
            //         (assessForm.value.skinNote
            //             ? ` (${assessForm.value.skinNote})`
            //             : ""),
            // };
        } else {
            assessForm.value = {
                vascularKind: "AVF",
                vascularPosLR: "L't",
                vascularPosCenter: "SCVC",
                permcathLen: "",
                vascularEval: ["正常"],
                vascularNote: "",
                consciousState: "清醒",
                consciousNote: "",
                skinState: "完整",
                skinLocation: "",
                skinSize: "",
                skinNote: "",
                note: "",
            };
        }
    } catch (e) {
        console.error("🔴 [Load Assessment Error]:", e);
    }
    assessActiveTab.value = tabIndex;
    showAssessModal.value = true;
};

// 血管評估選項
const evalOptions = [
    { label: "正常", val: "0" },
    { label: "紅", val: "1" },
    { label: "腫", val: "2" },
    { label: "熱", val: "3" },
    { label: "痛", val: "4" },
    { label: "滲血", val: "5" },
];

// 意識狀態選項
const consciousOptions = [
    { label: "清醒", val: "0" },
    { label: "嗜睡", val: "1" },
    { label: "木僵", val: "2" },
    { label: "半昏迷", val: "3" },
    { label: "昏迷", val: "4" },
    { label: "失智", val: "5" },
];

// 儲存邏輯 (多選)
const toggleVascularEval = (idx) => {
    // 血管通路評估共 6 個選項
    if (
        !assessForm.value.vascularEval ||
        assessForm.value.vascularEval.length !== 6
    ) {
        assessForm.value.vascularEval = ["1", "0", "0", "0", "0", "0"];
    }

    if (idx === 0) {
        // 點擊「正常」：強制設為 [1, 0, 0, 0, 0, 0]
        assessForm.value.vascularEval = ["1", "0", "0", "0", "0", "0"];
    } else {
        // 點擊「其他」：先移除正常位元，變更點擊位元
        assessForm.value.vascularEval[0] = "0";
        assessForm.value.vascularEval[idx] =
            assessForm.value.vascularEval[idx] === "1" ? "0" : "1";

        // 若全關，恢復預設正常
        if (!assessForm.value.vascularEval.includes("1")) {
            assessForm.value.vascularEval = ["1", "0", "0", "0", "0", "0"];
        }
    }
};

const saveUnifiedAssess = async () => {
    try {
        // 確保 vascularEval 包含正常且沒有其他，或是其他選項的組合
        // 按照規則：選項索引 (0-5) + 備註
        const evalIndices = [0, 1, 2, 3, 4, 5];
        // 按照規則：選項索引 (0-5) + 備註
        // 修正：vascularEval 本身現在就是長度為 6 的陣列 (['1', '0', '0', '0', '0', '0'])
        // 直接 join 即可
        const payload = {
            vascularKind: [
                assessForm.value.vascularKind,
                assessForm.value.vascularPosLR,
                assessForm.value.vascularPosCenter,
                assessForm.value.vascularKind === "Permcath"
                    ? assessForm.value.permcathLen || ""
                    : "",
                assessForm.value.vascularNote || "",
            ].join(","),
            vascularEval:
                assessForm.value.vascularEval.join(",") +
                "," +
                (assessForm.value.vascularNote || ""),
            consciousState:
                (consciousOptions.find(
                    (c) => c.label === assessForm.value.consciousState,
                )?.val || "0") +
                "," +
                (assessForm.value.consciousNote || ""),
            skinState:
                (assessForm.value.skinState === "完整" ? "0" : "1") +
                "," +
                (assessForm.value.skinLocation || "") +
                "," +
                (assessForm.value.skinSize || "") +
                "," +
                (assessForm.value.skinNote || ""),
        };

        console.log("Saving payload:", payload);

        await store.api.post(
            `/dialysis-checks/${store.currentPatient.id}/assessments`,
            payload,
        );

        // 更新 store.assessState 以便外面顯示
        store.assessState = {
            vascular: `${assessForm.value.vascularKind}${assessForm.value.vascularKind === "Permcath" && assessForm.value.permcathLen ? " " + assessForm.value.permcathLen + "cm" : ""} ${assessForm.value.vascularPosLR}${assessForm.value.vascularPosCenter} ${
                assessForm.value.vascularEval[0] === "1"
                    ? "正常"
                    : evalOptions
                          .filter(
                              (_, idx) =>
                                  assessForm.value.vascularEval[idx] === "1",
                          )
                          .map((o) => o.label)
                          .join(",")
            }${assessForm.value.vascularNote ? " (" + assessForm.value.vascularNote + ")" : ""}`,
            conscious:
                assessForm.value.consciousState +
                (assessForm.value.consciousNote
                    ? `(${assessForm.value.consciousNote})`
                    : ""),
            skin:
                assessForm.value.skinState +
                (assessForm.value.skinLocation || assessForm.value.skinSize
                    ? `(${assessForm.value.skinLocation}${assessForm.value.skinSize ? " " + assessForm.value.skinSize + "cm" : ""})`
                    : "") +
                (assessForm.value.skinNote
                    ? ` (${assessForm.value.skinNote})`
                    : ""),
        };

        showAssessModal.value = false;
        alert("✅ 三大評估已同步儲存至資料庫。");
    } catch (e) {
        console.error("🔴 [Assess Update Error]:", e);
        alert("⚠️ 儲存評估失敗。");
    }
};

// 新增透前給藥行
const showDrugModal = ref(false);
const newDrugForm = ref({ medicine_id: '', medicine: '', amount: 1, route_id: 1, note: '' });
const medicineOptions = ref([]);
const routeOptions = ref([]);

onMounted(async () => {
    try {
        const res = await store.api.get('/routes-of-administration');
        routeOptions.value = res.data;
    } catch (e) { console.error("載入途徑失敗:", e); }
});
// 模擬搜尋藥物 (實際開發應改為 API 呼叫)
const searchMedicines = async (query) => {
    if (!query) { medicineOptions.value = []; return; }
    try {
        const response = await store.api.get(`/medicines/search?q=${encodeURIComponent(query)}`);
        // 確保搜尋結果中包含 id，並正確賦值
        medicineOptions.value = response.data.map(m => ({ id: m.id, name: m.product_name }));
    } catch (e) { console.error("搜尋藥物失敗:", e); }
};

const selectMedicine = (medicine) => {
    newDrugForm.value.medicine = medicine.name;
    newDrugForm.value.medicine_id = medicine.id;
    medicineOptions.value = []; // 清空選項
};

const saveNewDrug = async () => {
    if (!newDrugForm.value.medicine) { alert("請輸入藥品名稱"); return; }
    
    const payload = {
        medicines: [{
            medicine_id: newDrugForm.value.medicine_id || 0,
            medicine: newDrugForm.value.medicine,
            amount: newDrugForm.value.amount,
            route_id: newDrugForm.value.route_id,
            note: newDrugForm.value.note
        }]
    };

    try {
        await store.api.post(`/dialysis-checks/${store.currentPatient.id}/medicines/pre`, payload);
        const time = new Date().toTimeString().slice(0, 5);
        preDrugs.value.push({
            text: `@${time} ${newDrugForm.value.medicine} ${newDrugForm.value.amount}顆`,
            isDeleted: false
        });
        showDrugModal.value = false;
        alert("✅ 透前給藥已儲存至後端。");
    } catch (e) {
        console.error("儲存透前給藥失敗:", e);
        alert("⚠️ 儲存失敗。");
    }
};

// 刪除透前給藥 (加線留痕行為)
const deletePreDrug = (idx) => {
    preDrugs.value[idx].isDeleted = true;
};

// 執行簽章與雙人連動（持久化至 patient_checks 資料表之 prepare_nurse_id 與 check_nurse_id）
const executeMainOnSign = async () => {
    if (isSignDisabled.value) return;

    if (store.mainSigned) {
        if (!confirm("⚠️ 確定要解除主責護理師 On-Sign 簽章嗎？若已有雙簽也將一併解除。")) {
            return;
        }
        await store.executeOnSign("main", "unsign");
    } else {
        const res = await store.executeOnSign("main", "sign");
        if (!res.success) {
            alert(res.message || "簽章失敗");
        }
    }
};

const triggerDoubleSign = async () => {
    if (!store.mainSigned) {
        alert("⚠️ 流程防呆：需先由主責護理師完成 On-Sign 後，才可由第二位護理師進行 Double Sign 雙簽！");
        return;
    }

    if (store.doubleSigned) {
        if (!confirm("⚠️ 確定要解除雙簽護理師 Double Sign 覆核嗎？")) {
            return;
        }
        await store.executeOnSign("double", "unsign");
    } else {
        const res = await store.executeOnSign("double", "sign");
        if (!res.success) {
            alert(res.message || "雙簽失敗");
        }
    }
};

// 監聽選中病患切換
watch(
    () => store.currentPatient?.id,
    () => {
        // 切換病患時資料由 selectPatient 自動自後端加載更新
    },
);
</script>

<style scoped>
.print-btn{
    background:white;
    color:var(--slate);
    border:1.5px solid var(--border);
    border-radius:7px;
    padding:4px 8px;
    font-size:11px;
    font-weight:600;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:3px;
    white-space:nowrap;
}
.incident-btn{
    background:var(--purple);
    color:white;
    border:none;
    border-radius:8px;
    padding:5px 10px;
    font-size:11px;
    font-weight:700;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:3px;
    white-space:nowrap;
}

/* ════ 100% 導入原稿 hismp_nursing_ui_v24.html 核心樣式 ════ */
.onsign-tab-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    box-sizing: border-box;
}

.confirm-bar {
    background: #f0fdf4;
    border: 1.5px solid #86efac; border:1.5px solid var(--border);
    border-radius:7px;
    padding:4px 8px;
    font-size:11px;
    font-weight:600;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:3px;
    white-space:nowrap;
}
.incident-btn{
    background:var(--purple);
    color:white;
    border:none;
    border-radius:8px;
    padding:5px 10px;
    font-size:11px;
    font-weight:700;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:3px;
    white-space:nowrap;
}

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
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-right: 36px; /* 留出右上角關閉按鈕空間，徹底防範遮擋 */
    gap: 6px;
    line-height: 1.4;
}
.modal-x {
    position: absolute;
    top: 14px;
    right: 14px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #64748b;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    z-index: 2;
}
.modal-x:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #dc2626;
}

/* ════ 生命徵象輸入彈窗（100% 傳承體重彈窗風格） ════ */
.modal-container-vsign {
    width: 94%;
    max-width: 520px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.25);
    border: 1.5px solid #99f6e4;
    overflow: hidden;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    text-align: left;
}

.vsign-modal-hdr {
    padding: 11px 16px;
    background: #f0fdfa;
    border-bottom: 1px solid #ccfbf1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #0f766e;
}

.vsign-hdr-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.vsign-hdr-title {
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
}

.vsign-pt-badge {
    background: #ccfbf1;
    color: #115e59;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 600;
}

.vsign-close-x {
    background: none;
    border: none;
    color: #0f766e;
    font-size: 20px;
    cursor: pointer;
    font-weight: bold;
    padding: 0 4px;
    line-height: 1;
    transition: color 0.15s ease;
}

.vsign-close-x:hover {
    color: #b91c1c;
}

.vsign-modal-body {
    padding: 14px 16px;
    overflow-y: auto;
    font-size: 13px;
    flex: 1;
}

.vsign-meta-desc {
    color: #475569;
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    margin-bottom: 12px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    border: 1px solid #e2e8f0;
}

.vsign-sec-card {
    background: #fafaf9;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
}

.vsign-sec-lbl {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.vsign-styled-input {
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 7px 10px;
    font-size: 13px;
    font-weight: 600;
    background: #ffffff;
    transition: all 0.15s ease;
}

.vsign-styled-input:focus {
    border-color: #0f766e !important;
    box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
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

/* 統一評估分頁樣式 (對齊體重彈窗切換膠囊風格) */
.assess-tab-btn {
    flex: 1;
    padding: 7px 10px;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    background: #f8fafc;
    color: #475569;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.assess-tab-btn:hover:not(.active) {
    background: #f1f5f9;
    border-color: #94a3b8;
}
.assess-tab-btn.active {
    border-color: #0f766e;
    background: #0f766e;
    color: white;
    box-shadow: 0 2px 4px rgba(15, 118, 110, 0.2);
}
.assess-opt-row {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 8px;
}
.assess-opt {
    font-size: 11.5px;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    padding: 3px 12px;
    cursor: pointer;
    background: white;
    color: #475569;
    user-select: none;
    font-weight: 600;
    transition: all 0.15s ease;
}
.assess-opt:hover:not(.sel) {
    background: #f1f5f9;
    border-color: #94a3b8;
}
.assess-opt.sel {
    background: #0f766e;
    border-color: #0f766e;
    color: white;
    box-shadow: 0 1px 3px rgba(15, 118, 110, 0.3);
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

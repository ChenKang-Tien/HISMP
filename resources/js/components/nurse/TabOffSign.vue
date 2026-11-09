<template>
    <div class="offsign-tab-wrapper">
        <!-- 三格大方塊生理數值填寫盤（臥姿/坐姿血壓之收縮壓與舒張壓分開輸入） -->
        <div class="bp2-grid">
            <!-- 1. 透後臥姿血壓 -->
            <div class="bp-box bp-box-input" @click="openLieBpModal">
                <div class="bp-box-top-row">
                    <div class="bpl">透後臥姿血壓</div>
                    <button class="bp-btn-pop" @click.stop="openLieBpModal" title="開啟臥姿血壓確認彈窗">確認/填寫</button>
                </div>
                <div class="bp-input-row" @click.stop>
                    <input
                        type="number"
                        class="bp-inline-input"
                        placeholder="收縮"
                        v-model="offVitals.lieSys"
                        @blur="formatBpPart('lieSys')"
                    />
                    <span class="bp-sep">/</span>
                    <input
                        type="number"
                        class="bp-inline-input"
                        placeholder="舒張"
                        v-model="offVitals.lieDia"
                        @blur="formatBpPart('lieDia')"
                    />
                </div>
                <div class="bp-sub-label">
                    <span>收縮壓 Sys</span>
                    <span>舒張壓 Dia</span>
                </div>
            </div>

            <!-- 2. 透後坐姿血壓 -->
            <div class="bp-box bp-box-input" @click="openSitBpModal">
                <div class="bp-box-top-row">
                    <div class="bpl">透後坐姿血壓</div>
                    <button class="bp-btn-pop" @click.stop="openSitBpModal" title="開啟坐姿血壓確認彈窗">確認/填寫</button>
                </div>
                <div class="bp-input-row" @click.stop>
                    <input
                        type="number"
                        class="bp-inline-input"
                        placeholder="收縮"
                        v-model="offVitals.sitSys"
                        @blur="formatBpPart('sitSys')"
                    />
                    <span class="bp-sep">/</span>
                    <input
                        type="number"
                        class="bp-inline-input"
                        placeholder="舒張"
                        v-model="offVitals.sitDia"
                        @blur="formatBpPart('sitDia')"
                    />
                </div>
                <div class="bp-sub-label">
                    <span>收縮壓 Sys</span>
                    <span>舒張壓 Dia</span>
                </div>
            </div>

            <!-- 3. 透後體重 -->
            <div
                class="bp-box"
                style="background: #f0fdfa; border-color: #99f6e4; cursor: pointer;"
                @click="fillPostWeight"
                title="點擊填寫/校正透後體重與加減項目"
            >
                <div class="bp-box-top-row">
                    <div class="bpl" style="color: #0f766e">透後體重 (淨重 / 已扣)</div>
                    <button class="bp-edit" @click.stop="fillPostWeight">過磅校正</button>
                </div>
                <div class="bpv" style="color: #0f766e">
                    {{
                        store.postAdjWeight !== null && store.postAdjWeight !== undefined
                            ? store.postAdjWeight + " kg"
                            : (store.postRawWeight ? store.postRawWeight + " kg" : "—")
                    }}
                </div>
                <div v-if="store.postRawWeight" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    未扣: {{ store.postRawWeight }} kg <span v-if="store.postDeductionTotal">| 調整: {{ (store.postDeductionTotal >= 0 ? '+' : '') + store.postDeductionTotal.toFixed(1) }}kg</span>
                </div>
            </div>
        </div>

        <!-- 檢核查對行：圓圈點選行 (每一項點擊皆跳出專屬 Modal 確認) -->
        <!-- 1. 透後過磅體重確認完成 -->
        <div
            class="chk"
            :class="{ done: checks.weight }"
            @click="openWeightCheckModal"
        >
            <div class="chk-c">{{ checks.weight ? "✓" : "" }}</div>
            <div class="chk-content">
                <div class="chk-title">透後過磅體重確認完成</div>
                <div v-if="checks.weight" class="chk-meta ok">
                    ✓ 體重已核實（未扣: {{ store.postRawWeight }}kg | 淨重: {{ netWeightDisplay }}kg | 脫水: {{ actualUfDisplay }}）
                </div>
                <div v-else class="chk-meta">
                    尚未核實體重與加減扣重項目
                </div>
            </div>
            <span class="chk-action-btn">{{ checks.weight ? "查看/校正" : "點入確認 ▸" }}</span>
        </div>

        <!-- 2. AK / Chamber 凝血核實完成 -->
        <div
            class="chk"
            :class="{ done: checks.clot }"
            @click="openClotModal"
        >
            <div class="chk-c">{{ checks.clot ? "✓" : "" }}</div>
            <div class="chk-content">
                <div class="chk-title">AK / Chamber 凝血核實完成（Clear）</div>
                <div v-if="checks.clot" class="chk-meta ok">
                    ✓ 已核實：AK [{{ clotData.ak === 'clear' ? 'Clear' : clotData.akSite || 'Clot' }}] / Chamber [{{ clotData.ch === 'clear' ? 'Clear' : clotData.chSite || clotData.ch }}]
                </div>
                <div v-else class="chk-meta">
                    點擊核實 AK 人工腎臟與 Chamber 氣室凝血狀態
                </div>
            </div>
            <span class="chk-action-btn">{{ checks.clot ? "查看/變更" : "點入核實 ▸" }}</span>
        </div>

        <!-- 3. 今日下機消耗醫材清點具名核對完成 -->
        <div
            class="chk"
            :class="{ done: checks.supply }"
            @click="openSupplyModal"
        >
            <div class="chk-c">{{ checks.supply ? "✓" : "" }}</div>
            <div class="chk-content">
                <div class="chk-title">今日下機消耗醫材清點具名核對完成</div>
                <div v-if="checks.supply" class="chk-meta ok">
                    ✓ 消耗醫材全數具名清點完畢（核對護理師：{{ supplyNurseName || currentNurseName }}）
                </div>
                <div v-else class="chk-meta">
                    點擊展開今日透析醫材清單並具名覆核
                </div>
            </div>
            <span class="chk-action-btn">{{ checks.supply ? "查看明細" : "點入清點 ▸" }}</span>
        </div>

        <!-- 4. 針孔壓迫止血安全確認 -->
        <div
            class="chk"
            :class="{ done: checks.needle }"
            @click="openNeedleModal"
        >
            <div class="chk-c">{{ checks.needle ? "✓" : "" }}</div>
            <div class="chk-content">
                <div class="chk-title">針孔壓迫止血安全確認（無滲血）</div>
                <div v-if="checks.needle" class="chk-meta ok">
                    ✓ 止血壓迫完成，穿刺處無滲血/血腫，末梢循環良好且血管震顫正常
                </div>
                <div v-else class="chk-meta">
                    點擊確認加壓止血時間、穿刺點狀況與末梢循環
                </div>
            </div>
            <span class="chk-action-btn">{{ checks.needle ? "查看評估" : "點入核實 ▸" }}</span>
        </div>

        <!-- 透後靜脈給藥 (完全對齊真實醫囑派發與臨櫃處置) -->
        <div class="post-drug-zone">
            <div class="post-drug-hdr">
                <div class="post-drug-hdr-left">
                    <i class="ti ti-needle"></i>透後靜脈給藥（醫囑派發）
                    <span v-if="postDrugList.length > 0" class="drug-count-badge">
                        {{ postDrugExecutedCount }}/{{ postDrugList.length }}
                    </span>
                </div>
                <button class="add-post-drug-btn" @click="openAddDrugModal">
                    ➕ 新增處置給藥
                </button>
            </div>

            <div v-if="postDrugList.length === 0" class="p-drug-empty">
                <span class="empty-icon">🟢</span>
                <span>本次透析無醫囑派發之透後靜脈給藥（若有臨時處置可點擊右側「➕ 新增處置給藥」）</span>
            </div>

            <div v-else class="post-drug-list">
                <div
                    v-for="(drug, index) in postDrugList"
                    :key="drug.id || drug.name + index"
                    class="post-drug-panel"
                >
                    <div class="post-drug-body">
                        <div class="p-drug-info">
                            <div class="p-drug-name">
                                {{ drug.name }}
                                <span v-if="drug.isManual" class="manual-badge">處置</span>
                            </div>
                            <div class="p-drug-meta">
                                {{ drug.route || 'IV' }} / {{ drug.amount || drug.dose || '依醫囑' }} ・ {{ drug.doctor || '醫師醫囑' }}{{ drug.note ? ' ・ ' + drug.note : '' }}
                            </div>
                        </div>
                        <div class="p-drug-actions" v-if="!drug.statusText && !drug.status">
                            <button
                                class="p-btn-exec"
                                @click="openDrugExecModal(drug)"
                            >
                                ☑ 執行
                            </button>
                            <button
                                class="p-btn-refuse"
                                @click="openDrugRefuseModal(drug)"
                            >
                                ✗ 拒打
                            </button>
                            <button
                                v-if="drug.isManual"
                                class="p-btn-del"
                                @click="deleteManualDrug(drug, index)"
                                title="刪除此筆新增給藥"
                            >
                                ✕
                            </button>
                        </div>
                        <div class="p-drug-status-row" v-else>
                            <div
                                :class="[
                                    'p-drug-status-text',
                                    drug.status === 'refuse' ? 'refused' : 'executed'
                                ]"
                            >
                                {{ drug.statusText }}
                            </div>
                            <button class="p-btn-reopen" @click="openDrugExecModal(drug)">
                                變更
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 終端下機簽章鈕 (點擊跳出總大核對確認 Modal) -->
        <button
            class="offsign-main-btn"
            :class="{ disabled: !isAllCompleted }"
            @click="openOffSignModal"
            :title="isAllCompleted ? '點擊開啟 Off-Sign 總大核對放行審查' : '請先完成上方所有血壓、體重、檢核查對與靜脈給藥項目'"
        >
            <span v-if="isAllCompleted">🛑 執行下機簽章 Off-Sign 總大核對</span>
            <span v-else>🔒 待完成所有項目後開放 Off-Sign ({{ completedCount }}/{{ totalCount }})</span>
        </button>

        <!-- ════════ 各項確認 MODALS ════════ -->

        <!-- Modal 1: 透後臥姿血壓確認 -->
        <div v-if="showLieBpModal" class="modal-overlay" @click.self="showLieBpModal = false">
            <div class="modal-container-off">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-activity" style="color: #0f766e;"></i>
                        <span>透後臥姿血壓量測確認</span>
                    </div>
                    <button class="modal-x" @click="showLieBpModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="pt-info-pill">
                        床號: {{ store.currentPatient?.bed || '—' }} | {{ store.currentPatient?.name || '—' }} ({{ store.currentPatient?.mr || '—' }})
                    </div>
                    <div class="field-hint">
                        透後臥姿血壓量測標準：請於平躺休息至少 3~5 分鐘後測量，正常收縮壓範圍建議為 100~140 mmHg，舒張壓為 60~90 mmHg。
                    </div>
                    <div class="bp-modal-grid">
                        <div class="bp-modal-col">
                            <label class="bp-modal-lbl">收縮壓 (Sys)</label>
                            <div class="bp-modal-input-wrap">
                                <input
                                    type="number"
                                    v-model="modalLieSys"
                                    placeholder="例如 128"
                                    class="bp-modal-input"
                                    @keyup.enter="saveLieBpModal"
                                />
                                <span class="bp-modal-unit">mmHg</span>
                            </div>
                        </div>
                        <div class="bp-modal-col">
                            <label class="bp-modal-lbl">舒張壓 (Dia)</label>
                            <div class="bp-modal-input-wrap">
                                <input
                                    type="number"
                                    v-model="modalLieDia"
                                    placeholder="例如 82"
                                    class="bp-modal-input"
                                    @keyup.enter="saveLieBpModal"
                                />
                                <span class="bp-modal-unit">mmHg</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="modalLieSys && modalLieDia" class="bp-preview-box">
                        當前輸入：<b style="color: #0f766e; font-size: 15px;">{{ modalLieSys }} / {{ modalLieDia }} mmHg</b>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showLieBpModal = false">取消</button>
                    <button class="mbtn-pri" @click="saveLieBpModal">✅ 確認臥姿血壓</button>
                </div>
            </div>
        </div>

        <!-- Modal 2: 透後坐姿血壓確認 -->
        <div v-if="showSitBpModal" class="modal-overlay" @click.self="showSitBpModal = false">
            <div class="modal-container-off">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-activity" style="color: #0f766e;"></i>
                        <span>透後坐姿血壓量測確認</span>
                    </div>
                    <button class="modal-x" @click="showSitBpModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="pt-info-pill">
                        床號: {{ store.currentPatient?.bed || '—' }} | {{ store.currentPatient?.name || '—' }} ({{ store.currentPatient?.mr || '—' }})
                    </div>
                    <div class="field-hint">
                        透後坐姿血壓量測標準：請病患採自然坐姿雙腳著地測量。若坐姿收縮壓較臥姿下降 ≥ 20 mmHg，須提防姿勢性低血壓。
                    </div>
                    <div class="bp-modal-grid">
                        <div class="bp-modal-col">
                            <label class="bp-modal-lbl">收縮壓 (Sys)</label>
                            <div class="bp-modal-input-wrap">
                                <input
                                    type="number"
                                    v-model="modalSitSys"
                                    placeholder="例如 125"
                                    class="bp-modal-input"
                                    @keyup.enter="saveSitBpModal"
                                />
                                <span class="bp-modal-unit">mmHg</span>
                            </div>
                        </div>
                        <div class="bp-modal-col">
                            <label class="bp-modal-lbl">舒張壓 (Dia)</label>
                            <div class="bp-modal-input-wrap">
                                <input
                                    type="number"
                                    v-model="modalSitDia"
                                    placeholder="例如 80"
                                    class="bp-modal-input"
                                    @keyup.enter="saveSitBpModal"
                                />
                                <span class="bp-modal-unit">mmHg</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="sitBpDropWarning" class="warning-callout">
                        {{ sitBpDropWarning }}
                    </div>
                    <div v-if="modalSitSys && modalSitDia" class="bp-preview-box">
                        當前輸入：<b style="color: #0f766e; font-size: 15px;">{{ modalSitSys }} / {{ modalSitDia }} mmHg</b>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showSitBpModal = false">取消</button>
                    <button class="mbtn-pri" @click="saveSitBpModal">✅ 確認坐姿血壓</button>
                </div>
            </div>
        </div>

        <!-- Modal 3: 透後過磅體重查核確認 -->
        <div v-if="showWeightCheckModal" class="modal-overlay" @click.self="showWeightCheckModal = false">
            <div class="modal-container-off" style="max-width: 500px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-scale" style="color: #0f766e;"></i>
                        <span>透後過磅體重查核確認</span>
                    </div>
                    <button class="modal-x" @click="showWeightCheckModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="pt-info-pill">
                        床號: {{ store.currentPatient?.bed || '—' }} | {{ store.currentPatient?.name || '—' }} ({{ store.currentPatient?.mr || '—' }})
                    </div>
                    <!-- 體重看板數據卡片 -->
                    <div class="wt-summary-grid">
                        <div class="wt-cell">
                            <span class="wt-lbl">乾體重 (DW)</span>
                            <span class="wt-val">{{ store.dryWeight || '—' }} kg</span>
                        </div>
                        <div class="wt-cell">
                            <span class="wt-lbl">透前淨重</span>
                            <span class="wt-val">{{ store.preAdjWeight || store.preRawWeight || '—' }} kg</span>
                        </div>
                        <div class="wt-cell highlight">
                            <span class="wt-lbl">透後過磅 (未扣)</span>
                            <span class="wt-val">{{ store.postRawWeight ? store.postRawWeight + ' kg' : '尚未過磅' }}</span>
                        </div>
                        <div class="wt-cell highlight">
                            <span class="wt-lbl">透後淨重 (已扣)</span>
                            <span class="wt-val">{{ netWeightDisplay }} kg</span>
                        </div>
                        <div class="wt-cell full-width">
                            <span class="wt-lbl">脫水量結算 (目標: {{ store.ufTarget || '—' }} kg)</span>
                            <span class="wt-val" style="color: #0f766e;">今日實際脫水量：{{ actualUfDisplay }}</span>
                        </div>
                    </div>

                    <div v-if="!store.postRawWeight || store.postRawWeight === '—'" class="warning-callout" style="margin-top: 10px;">
                        ⚠️ 目前尚未記錄透後過磅體重。請點擊「過磅校正」開啟體重管理面板進行填寫與扣重核對。
                    </div>

                    <div class="checklist-section" style="margin-top: 12px;">
                        <div class="sec-subtitle">臨床過磅查核項目（點擊確認）：</div>
                        <div
                            v-for="(item, idx) in weightChecklist"
                            :key="idx"
                            class="chk-item-row"
                            @click="item.checked = !item.checked"
                        >
                            <div class="chk-box-sq" :class="{ on: item.checked }">
                                {{ item.checked ? "✓" : "" }}
                            </div>
                            <span class="chk-item-label">{{ item.text }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showWeightCheckModal = false">取消</button>
                    <button class="mbtn-sec" style="color: #0f766e; border-color: #99f6e4; background: #f0fdfa;" @click="fillPostWeight">
                        ⚖️ 過磅校正
                    </button>
                    <button class="mbtn-pri" @click="saveWeightCheckModal">✅ 確認過磅體重無誤</button>
                </div>
            </div>
        </div>

        <!-- Modal 4: AK / Chamber 凝血核實確認 -->
        <div v-if="showClotModal" class="modal-overlay" @click.self="showClotModal = false">
            <div class="modal-container-off" style="max-width: 460px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-heart-rate-monitor" style="color: #0f766e;"></i>
                        <span>AK / Chamber 凝血核實確認</span>
                    </div>
                    <button class="modal-x" @click="showClotModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="pt-info-pill">
                        床號: {{ store.currentPatient?.bed || '—' }} | {{ store.currentPatient?.name || '—' }}
                    </div>

                    <div class="form-group-block">
                        <label class="form-sec-lbl">① 人工腎臟（AK Dialyzer）凝血狀態：</label>
                        <div class="radio-options-row">
                            <label class="radio-label">
                                <input type="radio" value="clear" v-model="clotData.ak" />
                                <span>Clear（無凝血，清澈）</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" value="clot" v-model="clotData.ak" />
                                <span>Clot（有凝固）</span>
                            </label>
                        </div>
                        <div v-if="clotData.ak === 'clot'" class="sub-input-row">
                            <span class="sub-input-lbl">凝血部位/程度：</span>
                            <input
                                type="text"
                                class="sub-text-input"
                                v-model="clotData.akSite"
                                placeholder="例如：中段微細凝血 10%"
                            />
                        </div>
                    </div>

                    <div class="form-group-block">
                        <label class="form-sec-lbl">② 靜脈壺 / 氣室（Chamber）凝血狀態：</label>
                        <div class="radio-options-row">
                            <label class="radio-label">
                                <input type="radio" value="clear" v-model="clotData.ch" />
                                <span>Clear（無凝血）</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" value="aclot" v-model="clotData.ch" />
                                <span>A.Clot（動脈端凝血）</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" value="vclot" v-model="clotData.ch" />
                                <span>V.Clot（靜脈端凝血）</span>
                            </label>
                        </div>
                        <div v-if="clotData.ch !== 'clear'" class="sub-input-row">
                            <span class="sub-input-lbl">凝血部位/程度：</span>
                            <input
                                type="text"
                                class="sub-text-input"
                                v-model="clotData.chSite"
                                placeholder="例如：濾網微量血塊"
                            />
                        </div>
                    </div>

                    <div class="form-group-block">
                        <label class="form-sec-lbl">臨床觀察備註（選填）：</label>
                        <input
                            type="text"
                            class="sub-text-input"
                            style="width: 100%; box-sizing: border-box;"
                            v-model="clotData.note"
                            placeholder="如：透析全程 Heparin 正常給予，回血平順無殘血"
                        />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showClotModal = false">取消</button>
                    <button class="mbtn-pri" @click="saveClotModal">✅ 確認完成核實</button>
                </div>
            </div>
        </div>

        <!-- Modal 5: 今日下機消耗醫材清點具名核對 -->
        <div v-if="showSupplyModal" class="modal-overlay" @click.self="showSupplyModal = false">
            <div class="modal-container-off" style="max-width: 480px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-clipboard-check" style="color: #0f766e;"></i>
                        <span>今日下機消耗醫材清點具名核對</span>
                    </div>
                    <button class="modal-x" @click="showSupplyModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="field-hint">
                        預設依本班次醫囑與處置帶入，全部已勾選。如有異常品項可點擊微調，清點無誤後由責任護理師具名確認封條。
                    </div>

                    <div class="supply-checklist-box">
                        <div
                            v-for="(item, idx) in supplyItems"
                            :key="idx"
                            class="supply-item-row"
                            @click="item.checked = !item.checked"
                        >
                            <div class="chk-box-sq" :class="{ on: item.checked }">
                                {{ item.checked ? "✓" : "" }}
                            </div>
                            <span class="supply-item-name">{{ item.name }}</span>
                            <span class="supply-item-qty">{{ item.qty }}</span>
                        </div>
                    </div>

                    <div class="signer-banner">
                        <i class="ti ti-user-check"></i>
                        <span>清點具名護理師：<b>{{ currentNurseName }}</b></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showSupplyModal = false">取消</button>
                    <button class="mbtn-pri" @click="saveSupplyModal">✅ 具名確認完成核對</button>
                </div>
            </div>
        </div>

        <!-- Modal 6: 穿刺針孔壓迫止血安全確認 -->
        <div v-if="showNeedleModal" class="modal-overlay" @click.self="showNeedleModal = false">
            <div class="modal-container-off" style="max-width: 480px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-bandage" style="color: #0f766e;"></i>
                        <span>穿刺針孔壓迫止血安全確認</span>
                    </div>
                    <button class="modal-x" @click="showNeedleModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="pt-info-pill">
                        床號: {{ store.currentPatient?.bed || '—' }} | {{ store.currentPatient?.name || '—' }}
                    </div>
                    <div class="field-hint">
                        拔針後請確實加壓止血，並審視血管通路狀態、穿刺處皮膚及肢端末梢循環，防止延遲性出血或血腫。
                    </div>

                    <div class="checklist-section">
                        <div
                            v-for="(item, idx) in needleChecklist"
                            :key="idx"
                            class="chk-item-row"
                            @click="item.checked = !item.checked"
                        >
                            <div class="chk-box-sq" :class="{ on: item.checked }">
                                {{ item.checked ? "✓" : "" }}
                            </div>
                            <span class="chk-item-label">{{ item.text }}</span>
                        </div>
                    </div>

                    <div class="form-group-block" style="margin-top: 10px;">
                        <label class="form-sec-lbl">護理觀察備註（選填）：</label>
                        <input
                            type="text"
                            class="sub-text-input"
                            style="width: 100%; box-sizing: border-box;"
                            v-model="needleNote"
                            placeholder="如：雙針孔壓迫良好無滲血，血管雜音良好，末梢溫暖"
                        />
                    </div>

                    <div class="signer-banner">
                        <i class="ti ti-user-check"></i>
                        <span>評估護理師：<b>{{ currentNurseName }}</b></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showNeedleModal = false">取消</button>
                    <button class="mbtn-pri" @click="saveNeedleModal">✅ 確認止血安全無誤</button>
                </div>
            </div>
        </div>

        <!-- Modal 7: 透後給藥執行記錄確認 -->
        <div v-if="showDrugExecModal" class="modal-overlay" @click.self="showDrugExecModal = false">
            <div class="modal-container-off" style="max-width: 440px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-syringe" style="color: #7c3aed;"></i>
                        <span>透後給藥執行記錄確認</span>
                    </div>
                    <button class="modal-x" @click="showDrugExecModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="drug-banner">
                        <div class="drug-name-lg">{{ drugForm.name }}</div>
                        <div class="drug-meta-sub">{{ drugForm.meta }}</div>
                    </div>

                    <div class="form-row-2col">
                        <div class="form-col">
                            <label class="form-sec-lbl">實際給藥劑量：</label>
                            <input
                                type="text"
                                class="sub-text-input"
                                v-model="drugForm.dose"
                                placeholder="如：4000u"
                            />
                        </div>
                        <div class="form-col">
                            <label class="form-sec-lbl">給藥途徑：</label>
                            <select class="sub-text-input" v-model="drugForm.route">
                                <option value="SC">SC（皮下注射）</option>
                                <option value="IV">IV（靜脈注射）</option>
                                <option value="IM">IM（肌肉注射）</option>
                                <option value="PO">PO（口服）</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group-block" style="margin-top: 10px;">
                        <label class="form-sec-lbl">不良反應觀察：</label>
                        <input
                            type="text"
                            class="sub-text-input"
                            style="width: 100%; box-sizing: border-box;"
                            v-model="drugForm.adverse"
                            placeholder="預設：正常"
                        />
                    </div>

                    <div class="signer-banner" style="margin-top: 12px;">
                        <i class="ti ti-user-check"></i>
                        <span>執行護理師：<b>{{ currentNurseName }}</b>（時間自動帶入）</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showDrugExecModal = false">取消</button>
                    <button class="mbtn-pri" style="background: #16a34a;" @click="saveDrugExecModal">✅ 確認執行給藥</button>
                </div>
            </div>
        </div>

        <!-- Modal 8: 透後給藥 — 拒打記錄確認 -->
        <div v-if="showDrugRefuseModal" class="modal-overlay" @click.self="showDrugRefuseModal = false">
            <div class="modal-container-off" style="max-width: 440px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-x" style="color: #dc2626;"></i>
                        <span>透後給藥 — 拒打記錄確認</span>
                    </div>
                    <button class="modal-x" @click="showDrugRefuseModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="drug-banner" style="background: #fef2f2; border-color: #fecaca;">
                        <div class="drug-name-lg" style="color: #dc2626;">{{ drugForm.name }} — 拒打</div>
                        <div class="drug-meta-sub">{{ drugForm.meta }}</div>
                    </div>

                    <div class="form-group-block" style="margin-top: 10px;">
                        <label class="form-sec-lbl">拒打原因（必填）：</label>
                        <textarea
                            class="sub-textarea"
                            rows="3"
                            v-model="drugRefuseReason"
                            placeholder="請填寫拒打原因（例如：病患主訴自覺疲倦今日暫緩、病患個人意願拒絕、醫師口頭指示暫停等）..."
                        ></textarea>
                    </div>

                    <div class="signer-banner" style="margin-top: 12px;">
                        <i class="ti ti-user-check"></i>
                        <span>記錄護理師：<b>{{ currentNurseName }}</b></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showDrugRefuseModal = false">取消</button>
                    <button class="mbtn-danger" @click="saveDrugRefuseModal">⚠️ 記錄拒打並存查</button>
                </div>
            </div>
        </div>

        <!-- Modal 8.5: 新增透後靜脈給藥處置 -->
        <div v-if="showAddDrugModal" class="modal-overlay" @click.self="showAddDrugModal = false">
            <div class="modal-container-off" style="max-width: 440px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-needle" style="color: #7c3aed;"></i>
                        <span>新增透後靜脈給藥處置</span>
                    </div>
                    <button class="modal-x" @click="showAddDrugModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group-block">
                        <label class="form-sec-lbl">藥品名稱（搜尋）：</label>
                        <div style="position: relative;">
                            <input
                                type="text"
                                class="sub-text-input"
                                style="width: 100%; box-sizing: border-box;"
                                placeholder="輸入藥名搜尋（如 Alinamin, B12, Cefazolin...）"
                                v-model="newDrugForm.medicine"
                                @input="searchMedicines($event.target.value)"
                            />
                            <div
                                v-if="medicineOptions.length > 0"
                                style="position: absolute; top: 100%; left: 0; right: 0; z-index: 999; border: 1px solid #cbd5e1; border-radius: 6px; max-height: 150px; overflow-y: auto; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                            >
                                <div
                                    v-for="m in medicineOptions"
                                    :key="m.id"
                                    @click="selectMedicine(m)"
                                    style="padding: 7px 10px; cursor: pointer; font-size: 12.5px; border-bottom: 1px solid #f1f5f9;"
                                    onmouseover="this.style.backgroundColor='#f1f5f9'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                >
                                    {{ m.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row-2col" style="margin-top: 10px;">
                        <div class="form-col">
                            <label class="form-sec-lbl">給藥劑量：</label>
                            <input
                                type="text"
                                class="sub-text-input"
                                v-model="newDrugForm.amount"
                                placeholder="如：1Amp 或 500mg"
                            />
                        </div>
                        <div class="form-col">
                            <label class="form-sec-lbl">給藥途徑：</label>
                            <select class="sub-text-input" v-model="newDrugForm.route">
                                <option value="IV">IV（靜脈注射）</option>
                                <option value="SC">SC（皮下注射）</option>
                                <option value="IM">IM（肌肉注射）</option>
                                <option value="PO">PO（口服）</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group-block" style="margin-top: 10px;">
                        <label class="form-sec-lbl">臨床處置備註（選填）：</label>
                        <input
                            type="text"
                            class="sub-text-input"
                            style="width: 100%; box-sizing: border-box;"
                            v-model="newDrugForm.note"
                            placeholder="如：透析結束前5分鐘自靜脈端慢注"
                        />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showAddDrugModal = false">取消</button>
                    <button class="mbtn-pri" style="background: #7c3aed;" @click="saveNewPostDrug">
                        ➕ 加入並接續執行
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal 9: Off-Sign 下機簽章總大核對確認 -->
        <div v-if="showOffSignModal" class="modal-overlay" @click.self="showOffSignModal = false">
            <div class="modal-container-off" style="max-width: 520px;">
                <div class="modal-hdr">
                    <div class="modal-title-text">
                        <i class="ti ti-shield-check" style="color: #0f766e;"></i>
                        <span>🛑 Off-Sign 下機簽章總大核對確認</span>
                    </div>
                    <button class="modal-x" @click="showOffSignModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <div class="pt-info-pill">
                        床號: {{ store.currentPatient?.bed || '—' }} | {{ store.currentPatient?.name || '—' }} ({{ store.currentPatient?.mr || '—' }})
                    </div>

                    <!-- 狀態一：未全數完成 (攔截鎖定) -->
                    <div v-if="!isAllCompleted">
                        <div class="warning-callout" style="margin-bottom: 12px;">
                            <b>🔒 下機簽章暫時鎖定：</b>尚有 <b>{{ totalCount - completedCount }}</b> 項未完成核對，全數 8 大項目核實無誤後方可放行簽章。
                        </div>

                        <div class="off-check-table">
                            <div
                                v-for="chk in offCheckSummaryList"
                                :key="chk.id"
                                class="off-check-row"
                                :class="{ ok: chk.done, fail: !chk.done }"
                                @click="handleJumpToItem(chk.id)"
                            >
                                <div class="off-check-icon">{{ chk.done ? '✓' : '⚠️' }}</div>
                                <div class="off-check-label">{{ chk.label }}</div>
                                <div class="off-check-val">{{ chk.value }}</div>
                                <div class="off-check-hint">{{ chk.done ? '已完成' : '點擊前往 ▸' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 狀態二：8 大項全數完成 (放行審查) -->
                    <div v-else>
                        <div class="success-callout" style="margin-bottom: 12px;">
                            <b>✅ 八大下機放行條件全數核查完畢！</b>請再次審查下列數據，確認無誤後點擊歸檔簽章。
                        </div>

                        <div class="off-check-table">
                            <div
                                v-for="chk in offCheckSummaryList"
                                :key="chk.id"
                                class="off-check-row ok"
                            >
                                <div class="off-check-icon">✓</div>
                                <div class="off-check-label">{{ chk.label }}</div>
                                <div class="off-check-val">{{ chk.value }}</div>
                            </div>
                        </div>

                        <div class="audit-notice-card" style="margin-top: 12px;">
                            <div class="audit-title"><i class="ti ti-file-certificate"></i> 法律與病歷稽核聲明：</div>
                            <div class="audit-text">
                                本班次病歷即將正式進行<b>封條鎖定</b>（病患狀態切換為已下機），並自動向護理病歷時間軸（PatientMidNurseRecord）寫入具名完整稽核日誌。
                            </div>
                        </div>

                        <div class="signer-banner" style="margin-top: 12px;">
                            <i class="ti ti-signature"></i>
                            <span>下機簽章護理師：<b style="color: #0f766e; font-size: 14px;">{{ currentNurseName }}</b></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="mbtn-sec" @click="showOffSignModal = false">
                        {{ isAllCompleted ? "返回檢查" : "返回依序完成" }}
                    </button>
                    <button
                        v-if="isAllCompleted"
                        class="mbtn-offsign"
                        @click="commitOffSignArchive"
                    >
                        🏁 正式執行下機簽章歸檔
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const emit = defineEmits(["open-weight-modal"]);
const store = useDialysisStore();

const currentNurseName = computed(() => {
    return (
        sessionStorage.getItem("hismp_user_name") ||
        localStorage.getItem("hismp_user_name") ||
        store.nurseName ||
        "楚心瑜"
    );
});

const offVitals = ref({
    lieSys: "",
    lieDia: "",
    sitSys: "",
    sitDia: "",
});

const formatBpPart = (field) => {
    if (offVitals.value[field]) {
        offVitals.value[field] = String(offVitals.value[field]).replace(/\D/g, "");
    }
};

const checks = ref({
    weight: false,
    clot: false,
    supply: false,
    needle: false,
});

const drugExecStatus = ref("");

// 1. 臥姿血壓 Modal 狀態
const showLieBpModal = ref(false);
const modalLieSys = ref("");
const modalLieDia = ref("");

const openLieBpModal = () => {
    modalLieSys.value = offVitals.value.lieSys || "";
    modalLieDia.value = offVitals.value.lieDia || "";
    showLieBpModal.value = true;
};

const saveLieBpModal = async () => {
    formatBpPart("lieSys");
    offVitals.value.lieSys = modalLieSys.value ? String(modalLieSys.value).replace(/\D/g, "") : "";
    offVitals.value.lieDia = modalLieDia.value ? String(modalLieDia.value).replace(/\D/g, "") : "";
    showLieBpModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'lie_bp',
                sys: offVitals.value.lieSys,
                dia: offVitals.value.lieDia,
            });
        } catch (e) {
            console.error("儲存臥姿血壓失敗", e);
        }
    }
};

// 2. 坐姿血壓 Modal 狀態
const showSitBpModal = ref(false);
const modalSitSys = ref("");
const modalSitDia = ref("");

const openSitBpModal = () => {
    modalSitSys.value = offVitals.value.sitSys || "";
    modalSitDia.value = offVitals.value.sitDia || "";
    showSitBpModal.value = true;
};

const saveSitBpModal = async () => {
    offVitals.value.sitSys = modalSitSys.value ? String(modalSitSys.value).replace(/\D/g, "") : "";
    offVitals.value.sitDia = modalSitDia.value ? String(modalSitDia.value).replace(/\D/g, "") : "";
    showSitBpModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'sit_bp',
                sys: offVitals.value.sitSys,
                dia: offVitals.value.sitDia,
            });
        } catch (e) {
            console.error("儲存坐姿血壓失敗", e);
        }
    }
};

const sitBpDropWarning = computed(() => {
    const sit = parseInt(modalSitSys.value, 10);
    const lieActual = parseInt(offVitals.value.lieSys, 10);
    if (!isNaN(lieActual) && !isNaN(sit)) {
        const drop = lieActual - sit;
        if (drop >= 20) {
            return `⚠️ 提醒：坐姿收縮壓較臥姿下降 ${drop} mmHg (≥20 mmHg)，疑似姿勢性低血壓風險，請提醒病患起身時放緩腳步！`;
        }
    }
    return "";
});

// 3. 體重查核 Modal 狀態
const showWeightCheckModal = ref(false);
const weightChecklist = ref([
    { text: "磅秤已確認歸零校準，病患已確實站立/坐輪椅過磅", checked: true },
    { text: "輪椅、被單、衣物與加減項目已如實扣除核算", checked: true },
    { text: "實際脫水量與脫水目標相符，無過度脫水現象", checked: true },
]);

const openWeightCheckModal = () => {
    showWeightCheckModal.value = true;
};

const saveWeightCheckModal = async () => {
    if (!store.postRawWeight || store.postRawWeight === "—") {
        alert("請先填寫透後體重");
        fillPostWeight();
        return;
    }
    checks.value.weight = true;
    showWeightCheckModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'weight',
                weight: parseFloat(store.postRawWeight) || 0,
            });
        } catch (e) {
            console.error("儲存透後體重核實失敗", e);
        }
    }
};

const fillPostWeight = () => {
    emit("open-weight-modal", "post");
};

// 4. AK / Chamber 凝血核實 Modal 狀態
const showClotModal = ref(false);
const clotData = ref({
    ak: "clear",
    akSite: "",
    ch: "clear",
    chSite: "",
    note: "",
});

const openClotModal = () => {
    showClotModal.value = true;
};

const saveClotModal = async () => {
    checks.value.clot = true;
    showClotModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'clot',
                ak: clotData.value.ak,
                akSite: clotData.value.akSite,
                ch: clotData.value.ch,
                chSite: clotData.value.chSite,
                note: clotData.value.note,
            });
        } catch (e) {
            console.error("儲存凝血核實失敗", e);
        }
    }
};

// 5. 消耗醫材清點 Modal 狀態
const showSupplyModal = ref(false);
const supplyNurseName = ref("");
const supplyItems = ref([
    { name: "FX80 Classix（Dialyzer 人工腎臟）", qty: "× 1", checked: true },
    { name: "透析管路組（Blood Tubing Line）", qty: "× 1", checked: true },
    { name: "穿刺針（AV Fistula Needle）", qty: "× 2", checked: true },
    { name: "生理食鹽水 N/S 500ml", qty: "× 1", checked: true },
    { name: "肝素 Heparin 2000u", qty: "× 1", checked: true },
    { name: "止血加壓帶 / 紗布棉球", qty: "× 1", checked: true },
]);

const openSupplyModal = () => {
    showSupplyModal.value = true;
};

const saveSupplyModal = async () => {
    supplyNurseName.value = currentNurseName.value;
    checks.value.supply = true;
    showSupplyModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'supply',
                items: supplyItems.value,
            });
        } catch (e) {
            console.error("儲存醫材清點失敗", e);
        }
    }
};

// 6. 針孔止血安全確認 Modal 狀態
const showNeedleModal = ref(false);
const needleNote = ref("");
const needleChecklist = ref([
    { text: "穿刺針孔已確實加壓止血滿 10-15 分鐘", checked: true },
    { text: "檢視穿刺針孔處已完全止血，無持續滲血", checked: true },
    { text: "局部穿刺部位皮膚無皮下血腫形成", checked: true },
    { text: "血管通路震顫（Thrill）及血管雜音（Bruit）確認正常", checked: true },
    { text: "穿刺肢端末梢循環良好（膚色粉紅、皮溫正常、無發紺）", checked: true },
    { text: "止血加壓帶鬆緊適度，已向病患完成離院衛教提醒", checked: true },
]);

const openNeedleModal = () => {
    showNeedleModal.value = true;
};

const saveNeedleModal = async () => {
    checks.value.needle = true;
    showNeedleModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'needle',
                pinholeBlood: 0,
                note: needleNote.value,
            });
        } catch (e) {
            console.error("儲存針孔止血安全確認失敗", e);
        }
    }
};

// 7 & 8. 透後給藥執行與拒打 Modal 狀態
const showDrugExecModal = ref(false);
const showDrugRefuseModal = ref(false);
const showAddDrugModal = ref(false);
const postDrugList = ref([]);
const activeDrug = ref(null);
const drugRefuseReason = ref("");
const drugForm = ref({
    name: "",
    meta: "",
    dose: "",
    route: "IV",
    adverse: "正常",
});
const newDrugForm = ref({
    medicine_id: null,
    medicine: "",
    amount: "1",
    route: "IV",
    note: "",
});
const medicineOptions = ref([]);

const searchMedicines = async (query) => {
    if (!query) {
        medicineOptions.value = [];
        return;
    }
    try {
        const response = await store.api.get(`/medicines/search?q=${encodeURIComponent(query)}`);
        medicineOptions.value = response.data.map(m => ({ id: m.id, name: m.product_name }));
    } catch (e) {
        console.error("搜尋藥物失敗:", e);
    }
};

const selectMedicine = (medicine) => {
    newDrugForm.value.medicine = medicine.name;
    newDrugForm.value.medicine_id = medicine.id;
    medicineOptions.value = [];
};

const openAddDrugModal = () => {
    newDrugForm.value = {
        medicine_id: null,
        medicine: "",
        amount: "1",
        route: "IV",
        note: "",
    };
    medicineOptions.value = [];
    showAddDrugModal.value = true;
};

const saveNewPostDrug = () => {
    if (!newDrugForm.value.medicine.trim()) {
        alert("請輸入或選擇藥品名稱！");
        return;
    }
    const newDrug = {
        id: null,
        medicine_id: newDrugForm.value.medicine_id,
        name: newDrugForm.value.medicine.trim(),
        amount: newDrugForm.value.amount || "1",
        dose: newDrugForm.value.amount || "1",
        route: newDrugForm.value.route || "IV",
        adverse: "正常",
        reason: "",
        doctor: "護理處置",
        note: newDrugForm.value.note || "臨櫃新增",
        status: null,
        statusText: "",
        isManual: true,
    };
    postDrugList.value.push(newDrug);
    showAddDrugModal.value = false;
    openDrugExecModal(newDrug);
};

const deleteManualDrug = async (drug, index) => {
    if (!confirm(`確定要刪除「${drug.name}」給藥項目嗎？`)) return;
    if (drug.id && store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'drug',
                status: 'delete',
                id: drug.id,
                name: drug.name
            });
        } catch (e) {
            console.error("刪除處置給藥失敗", e);
        }
    }
    postDrugList.value.splice(index, 1);
};

const postDrugExecutedCount = computed(() => {
    return postDrugList.value.filter(d => !!d.status).length;
});

const isDrugCompleted = computed(() => {
    if (!postDrugList.value || postDrugList.value.length === 0) {
        return true;
    }
    return postDrugList.value.every(d => !!d.status);
});

const openDrugExecModal = (drug) => {
    const target = drug || postDrugList.value[0] || {
        name: "透後靜脈給藥",
        meta: "IV / 依醫囑",
        dose: "1",
        route: "IV",
        adverse: "正常",
    };
    activeDrug.value = target;
    drugForm.value.name = target.name;
    drugForm.value.meta = `${target.route || 'IV'} / ${target.amount || target.dose || '依醫囑'} ・ ${target.doctor || '醫師醫囑'}${target.note ? ' ・ ' + target.note : ''}`;
    drugForm.value.dose = target.dose || target.amount || "";
    drugForm.value.route = target.route || "IV";
    drugForm.value.adverse = target.adverse || "正常";
    showDrugExecModal.value = true;
};

const saveDrugExecModal = async () => {
    const time = new Date().toTimeString().slice(0, 5);
    const nurse = currentNurseName.value;
    const doseText = drugForm.value.dose ? drugForm.value.dose : "依醫囑";
    const adverseText = drugForm.value.adverse || "正常";
    const statusText = `✅ ${nurse} ${time} 已執行（${drugForm.value.route} ${doseText}，不良反應: ${adverseText}）`;

    if (activeDrug.value) {
        activeDrug.value.status = 'execute';
        activeDrug.value.statusText = statusText;
        activeDrug.value.dose = drugForm.value.dose;
        activeDrug.value.route = drugForm.value.route;
        activeDrug.value.adverse = adverseText;
    }
    drugExecStatus.value = statusText;
    showDrugExecModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            const res = await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'drug',
                status: 'execute',
                medicine_id: activeDrug.value?.medicine_id || null,
                name: activeDrug.value?.name || drugForm.value.name,
                dose: drugForm.value.dose,
                route: drugForm.value.route,
                adverse: drugForm.value.adverse,
                id: activeDrug.value?.id || null,
            });
            if (res.data?.record?.id && activeDrug.value) {
                activeDrug.value.id = res.data.record.id;
            }
            if (res.data?.nursing_record) {
                store.addNursingRecord(res.data.nursing_record.value || res.data.nursing_record.content);
            }
        } catch (e) {
            console.error("儲存給藥執行記錄失敗", e);
        }
    }
};

const openDrugRefuseModal = (drug) => {
    const target = drug || postDrugList.value[0] || {
        name: "透後靜脈給藥",
        meta: "IV / 依醫囑",
        dose: "1",
        route: "IV",
    };
    activeDrug.value = target;
    drugForm.value.name = target.name;
    drugForm.value.meta = `${target.route || 'IV'} / ${target.amount || target.dose || '依醫囑'} ・ ${target.doctor || '醫師醫囑'}${target.note ? ' ・ ' + target.note : ''}`;
    drugRefuseReason.value = target.reason || "";
    showDrugRefuseModal.value = true;
};

const saveDrugRefuseModal = async () => {
    if (!drugRefuseReason.value.trim()) {
        alert("請填寫拒打原因！");
        return;
    }
    const time = new Date().toTimeString().slice(0, 5);
    const nurse = currentNurseName.value;
    const reason = drugRefuseReason.value.trim();
    const statusText = `❌ ${nurse} ${time} 拒打（原因: ${reason}）`;

    if (activeDrug.value) {
        activeDrug.value.status = 'refuse';
        activeDrug.value.statusText = statusText;
        activeDrug.value.reason = reason;
    }
    drugExecStatus.value = statusText;
    showDrugRefuseModal.value = false;

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            const res = await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign-item`, {
                type: 'drug',
                status: 'refuse',
                medicine_id: activeDrug.value?.medicine_id || null,
                name: activeDrug.value?.name || drugForm.value.name,
                reason: reason,
                id: activeDrug.value?.id || null,
            });
            if (res.data?.record?.id && activeDrug.value) {
                activeDrug.value.id = res.data.record.id;
            }
            if (res.data?.nursing_record) {
                store.addNursingRecord(res.data.nursing_record.value || res.data.nursing_record.content);
            }
        } catch (e) {
            console.error("儲存給藥拒打記錄失敗", e);
        }
    }
};

// 9. Off-Sign 總大核對確認 Modal 狀態
const showOffSignModal = ref(false);

const openOffSignModal = () => {
    showOffSignModal.value = true;
};

// 數據格式化與總結
const lieBPFormatted = computed(() => {
    if (offVitals.value.lieSys && offVitals.value.lieDia) {
        return `${offVitals.value.lieSys}/${offVitals.value.lieDia}`;
    }
    return "";
});

const sitBPFormatted = computed(() => {
    if (offVitals.value.sitSys && offVitals.value.sitDia) {
        return `${offVitals.value.sitSys}/${offVitals.value.sitDia}`;
    }
    return "";
});

const netWeightDisplay = computed(() => {
    if (store.postAdjWeight !== null && store.postAdjWeight !== undefined) {
        return store.postAdjWeight;
    }
    return store.postRawWeight || "—";
});

const actualUfDisplay = computed(() => {
    if (store.actualUfWeight !== null && store.actualUfWeight !== undefined) {
        return store.actualUfWeight + " kg";
    }
    return "—";
});

const isAllCompleted = computed(() => {
    const hasLieBP = !!(offVitals.value.lieSys && offVitals.value.lieDia);
    const hasSitBP = !!(offVitals.value.sitSys && offVitals.value.sitDia);
    const hasWeight = !!store.postRawWeight && store.postRawWeight !== "—";
    const allChecksDone =
        checks.value.weight &&
        checks.value.clot &&
        checks.value.supply &&
        checks.value.needle;
    const drugDone = isDrugCompleted.value;
    return hasLieBP && hasSitBP && hasWeight && allChecksDone && drugDone;
});

const completedCount = computed(() => {
    let count = 0;
    if (offVitals.value.lieSys && offVitals.value.lieDia) count++;
    if (offVitals.value.sitSys && offVitals.value.sitDia) count++;
    if (store.postRawWeight && store.postRawWeight !== "—") count++;
    if (checks.value.weight) count++;
    if (checks.value.clot) count++;
    if (checks.value.supply) count++;
    if (checks.value.needle) count++;
    if (isDrugCompleted.value) count++;
    return count;
});

const totalCount = computed(() => 8);

const offCheckSummaryList = computed(() => {
    return [
        {
            id: "lieBp",
            label: "① 透後臥姿血壓",
            done: !!(offVitals.value.lieSys && offVitals.value.lieDia),
            value: (offVitals.value.lieSys && offVitals.value.lieDia) ? `${offVitals.value.lieSys}/${offVitals.value.lieDia} mmHg` : "尚未填寫",
        },
        {
            id: "sitBp",
            label: "② 透後坐姿血壓",
            done: !!(offVitals.value.sitSys && offVitals.value.sitDia),
            value: (offVitals.value.sitSys && offVitals.value.sitDia) ? `${offVitals.value.sitSys}/${offVitals.value.sitDia} mmHg` : "尚未填寫",
        },
        {
            id: "weightVal",
            label: "③ 透後體重過磅",
            done: !!store.postRawWeight && store.postRawWeight !== "—",
            value: (store.postRawWeight && store.postRawWeight !== "—") ? `未扣:${store.postRawWeight}kg / 淨重:${netWeightDisplay.value}kg` : "尚未過磅",
        },
        {
            id: "weightChk",
            label: "④ 體重核實確認",
            done: checks.value.weight,
            value: checks.value.weight ? "已核實無誤" : "待確認",
        },
        {
            id: "clot",
            label: "⑤ AK / Chamber 凝血",
            done: checks.value.clot,
            value: checks.value.clot ? `AK [${clotData.value.ak === 'clear' ? 'Clear' : clotData.value.akSite || 'Clot'}] / Chamber [${clotData.value.ch === 'clear' ? 'Clear' : clotData.value.chSite || clotData.value.ch}]` : "待核實",
        },
        {
            id: "supply",
            label: "⑥ 消耗醫材清點",
            done: checks.value.supply,
            value: checks.value.supply ? `已全數具名清點 (${supplyNurseName.value || currentNurseName.value})` : "待清點",
        },
        {
            id: "needle",
            label: "⑦ 針孔壓迫止血",
            done: checks.value.needle,
            value: checks.value.needle ? "安全無滲血，血管震顫正常" : "待確認",
        },
        {
            id: "drug",
            label: "⑧ 透後靜脈給藥",
            done: isDrugCompleted.value,
            value: (function() {
                if (!postDrugList.value || postDrugList.value.length === 0) {
                    return "🟢 本次無醫囑派發給藥";
                }
                const doneCount = postDrugList.value.filter(d => !!d.status).length;
                if (doneCount === postDrugList.value.length) {
                    return `✅ 全數核實完成 (${doneCount}/${postDrugList.value.length})`;
                }
                return `尚有 ${postDrugList.value.length - doneCount} 項未核實`;
            })(),
        },
    ];
});

const handleJumpToItem = (id) => {
    showOffSignModal.value = false;
    if (id === "lieBp") openLieBpModal();
    else if (id === "sitBp") openSitBpModal();
    else if (id === "weightVal") fillPostWeight();
    else if (id === "weightChk") openWeightCheckModal();
    else if (id === "clot") openClotModal();
    else if (id === "supply") openSupplyModal();
    else if (id === "needle") openNeedleModal();
    else if (id === "drug") {
        const pending = postDrugList.value.find(d => !d.status);
        if (pending) {
            openDrugExecModal(pending);
        } else if (postDrugList.value.length > 0) {
            openDrugExecModal(postDrugList.value[0]);
        } else {
            openAddDrugModal();
        }
    }
};

const commitOffSignArchive = async () => {
    const netWeight = store.postAdjWeight !== null && store.postAdjWeight !== undefined ? store.postAdjWeight : store.postRawWeight;
    const actualUf = store.actualUfWeight !== null && store.actualUfWeight !== undefined ? store.actualUfWeight + "kg" : "—";

    if (store.currentPatient?.id && store.currentPatient.id !== "—") {
        try {
            await store.api.post(`/dialysis-checks/${store.currentPatient.id}/off-sign`, {
                lieBP: lieBPFormatted.value,
                sitBP: sitBPFormatted.value,
                weight: parseFloat(store.postRawWeight) || 0,
                checks: {
                    ...checks.value,
                    clotData: clotData.value,
                    supplyNurse: supplyNurseName.value || currentNurseName.value,
                    drugExecStatus: drugExecStatus.value,
                },
            });
        } catch (err) {
            console.error("Off-Sign API 儲存失敗:", err);
        }
    }

    store.addNursingRecord(
        `[下機作業] 安全下機歸檔。透後臥姿血壓:${lieBPFormatted.value}, 坐姿血壓:${sitBPFormatted.value}, 透後未扣:${store.postRawWeight}kg, 透後淨重:${netWeight}kg, 實際脫水:${actualUf}。下機所有查核項目（體重、凝血、醫材、止血、靜脈給藥）全數核查無誤。`,
    );

    if (store.currentPatient) {
        store.currentPatient.status = 3;
        store.currentPatient.statusText = "🏁 已下機";
    }

    showOffSignModal.value = false;
    alert("🏁 歸檔成功！本班次病歷已正式封條鎖定。");
};

const loadSavedData = async () => {
    if (!store.currentPatient?.id || store.currentPatient.id === "—") return;
    try {
        const res = await store.api.get(`/dialysis-checks/${store.currentPatient.id}`);
        if (res.data && res.data.off_sign_data) {
            const vit = res.data.off_sign_data.vitals || {};
            if (vit.lieSys !== undefined && vit.lieDia !== undefined) {
                offVitals.value.lieSys = vit.lieSys;
                offVitals.value.lieDia = vit.lieDia;
            } else if (vit.lieBP) {
                const parts = vit.lieBP.split("/");
                offVitals.value.lieSys = parts[0] || "";
                offVitals.value.lieDia = parts[1] || "";
            }

            if (vit.sitSys !== undefined && vit.sitDia !== undefined) {
                offVitals.value.sitSys = vit.sitSys;
                offVitals.value.sitDia = vit.sitDia;
            } else if (vit.sitBP) {
                const parts = vit.sitBP.split("/");
                offVitals.value.sitSys = parts[0] || "";
                offVitals.value.sitDia = parts[1] || "";
            }

            store.postRawWeight = res.data.off_sign_data.weight;
            if (res.data.off_sign_data.checks) {
                checks.value = { ...checks.value, ...res.data.off_sign_data.checks };
            }
            if (res.data.off_sign_data.clotData) {
                clotData.value = { ...clotData.value, ...res.data.off_sign_data.clotData };
            }
            if (res.data.off_sign_data.supplyNurse) {
                supplyNurseName.value = res.data.off_sign_data.supplyNurse;
            }
            if (res.data.off_sign_data.post_dialysis_drugs) {
                postDrugList.value = res.data.off_sign_data.post_dialysis_drugs;
            } else {
                postDrugList.value = [];
            }
            if (res.data.off_sign_data.drugExecStatus) {
                drugExecStatus.value = res.data.off_sign_data.drugExecStatus;
            }
            if (res.data.off_sign_data.drugForm) {
                drugForm.value = { ...drugForm.value, ...res.data.off_sign_data.drugForm };
            }
            store.calculateWeights();
        }
    } catch (e) {
        console.error("無法載入下機資料", e);
    }
};

watch(
    () => store.currentPatient?.id,
    (newId) => {
        if (newId && newId !== "—") {
            loadSavedData();
        }
    }
);

watch(
    () => store.postRawWeight,
    (val) => {
        if (val && val !== "—") {
            checks.value.weight = true;
        }
    },
    { immediate: true },
);

onMounted(loadSavedData);
</script>

<style scoped>
.offsign-tab-wrapper {
    position: relative;
    padding-bottom: 12px;
}

.bp2-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 6px;
    margin-bottom: 8px;
}

.bp-box {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px;
    text-align: center;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.15s ease;
}

.bp-box:hover {
    border-color: #0f766e;
    box-shadow: 0 2px 4px rgba(15, 118, 110, 0.08);
}

.bp-box-input {
    background: #fafaf9;
    cursor: pointer;
}

.bp-box-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3px;
}

.bp-btn-pop {
    font-size: 10px;
    color: #0f766e;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 4px;
    padding: 1px 5px;
    cursor: pointer;
    font-weight: 600;
}

.bp-btn-pop:hover {
    background: #ccfbf1;
}

.bp-input-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin: 4px 0 2px;
}

.bp-inline-input {
    width: 48px;
    height: 28px;
    text-align: center;
    font-size: 15px;
    font-weight: 700;
    color: #0f766e;
    border: 1.5px solid #cbd5e1;
    border-radius: 5px;
    outline: none;
    background: #ffffff;
    box-sizing: border-box;
    transition: all 0.15s ease;
}

.bp-inline-input:focus {
    border-color: #0f766e;
    box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
}

.bp-inline-input::placeholder {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

.bp-sep {
    font-size: 16px;
    font-weight: 700;
    color: #94a3b8;
    user-select: none;
}

.bp-sub-label {
    display: flex;
    justify-content: space-between;
    padding: 0 4px;
    font-size: 9px;
    color: #94a3b8;
    font-weight: 600;
}

.bp-box .bpl {
    font-size: 10.5px;
    color: #64748b;
    font-weight: 600;
}

.bp-box .bpv {
    font-size: 16px;
    font-weight: 700;
    color: #0f766e;
}

.bp-box .bp-edit {
    font-size: 10px;
    color: #0f766e;
    background: #ffffff;
    border: 1px solid #99f6e4;
    border-radius: 4px;
    padding: 1px 6px;
    cursor: pointer;
    font-weight: 600;
}

.bp-box .bp-edit:hover {
    background: #ccfbf1;
}

/* 檢核項列表 */
.chk {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 10px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 5px;
    cursor: pointer;
    font-size: 13px;
    background: white;
    user-select: none;
    transition: all 0.15s ease;
}

.chk:hover {
    border-color: #0f766e;
    background: #f8fafc;
}

.chk.done {
    background: #f0fdf4;
    border-color: #86efac;
}

.chk-c {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 700;
    color: white;
    transition: all 0.15s ease;
}

.chk.done .chk-c {
    background: #16a34a;
    border-color: #16a34a;
}

.chk-content {
    flex: 1;
    min-width: 0;
}

.chk-title {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
}

.chk-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 1px;
}

.chk-meta.ok {
    color: #16a34a;
    font-weight: 600;
}

.chk-action-btn {
    font-size: 11.5px;
    color: #0f766e;
    font-weight: 600;
    flex-shrink: 0;
}

/* 給藥區塊 */
.post-drug-zone {
    margin-top: 8px;
    border-top: 1.5px solid #e2e8f0;
    padding-top: 7px;
}

.post-drug-hdr {
    font-size: 11.5px;
    font-weight: 700;
    color: #7c3aed;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.post-drug-hdr-left {
    display: flex;
    align-items: center;
    gap: 6px;
}

.drug-count-badge {
    font-size: 10px;
    background: #ede9fe;
    color: #7c3aed;
    border-radius: 9999px;
    padding: 1px 6px;
    font-weight: 700;
}

.add-post-drug-btn {
    font-size: 11px;
    font-weight: 600;
    color: #7c3aed;
    background: #f5f3ff;
    border: 1px solid #c4b5fd;
    border-radius: 5px;
    padding: 2px 8px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.add-post-drug-btn:hover {
    background: #ede9fe;
    border-color: #a78bfa;
}

.p-drug-empty {
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12px;
    color: #059669;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.post-drug-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.manual-badge {
    font-size: 10px;
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
    border-radius: 4px;
    padding: 1px 4px;
    margin-left: 5px;
    font-weight: 600;
}

.p-btn-del {
    font-size: 11px;
    background: #ffffff;
    color: #94a3b8;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 3px 6px;
    cursor: pointer;
}

.p-btn-del:hover {
    color: #ef4444;
    border-color: #fca5a5;
    background: #fef2f2;
}

.post-drug-panel {
    background: #f5f3ff;
    border: 1.5px solid #ddd6fe;
    border-radius: 8px;
    padding: 7px 10px;
}

.post-drug-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.p-drug-name {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
}

.p-drug-meta {
    font-size: 10.5px;
    color: #64748b;
    margin-top: 1px;
}

.p-drug-actions {
    display: flex;
    align-items: center;
    gap: 5px;
}

.p-btn-exec {
    font-size: 11px;
    background: #f0fdf4;
    color: #16a34a;
    border: 1.5px solid #86efac;
    border-radius: 5px;
    padding: 3px 9px;
    cursor: pointer;
    font-weight: 700;
}

.p-btn-exec:hover {
    background: #dcfce7;
}

.p-btn-refuse {
    font-size: 11px;
    background: #fef2f2;
    color: #b91c1c;
    border: 1.5px solid #fecaca;
    border-radius: 5px;
    padding: 3px 9px;
    cursor: pointer;
    font-weight: 700;
}

.p-btn-refuse:hover {
    background: #fee2e2;
}

.p-drug-status-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.p-drug-status-text {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}

.p-drug-status-text.refused {
    color: #dc2626;
}

.p-drug-status-text.executed {
    color: #16a34a;
}

.p-btn-reopen {
    font-size: 10.5px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 1px 6px;
    cursor: pointer;
    color: #64748b;
}

/* 簽章大鈕 */
.offsign-main-btn {
    width: 100%;
    background: #0f766e;
    color: white;
    border: none;
    border-radius: 9px;
    padding: 12px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 4px 6px -1px rgba(15, 118, 110, 0.2);
    transition: all 0.15s ease;
}

.offsign-main-btn:hover:not(.disabled) {
    background: #115e59;
    box-shadow: 0 6px 10px -1px rgba(15, 118, 110, 0.3);
}

.offsign-main-btn.disabled {
    background: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
    opacity: 0.85;
}

/* 移除 number input 的上下小箭頭 */
.bp-inline-input::-webkit-outer-spin-button,
.bp-inline-input::-webkit-inner-spin-button,
.bp-modal-input::-webkit-outer-spin-button,
.bp-modal-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.bp-inline-input,
.bp-modal-input {
    -moz-appearance: textfield;
}

/* ════════ 全域 MODAL 樣式 ════════ */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
    padding: 16px;
    box-sizing: border-box;
}

.modal-container-off {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border: 1.5px solid #cbd5e1;
    width: 100%;
    max-width: 460px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modal-pop 0.18s ease-out;
}

@keyframes modal-pop {
    from {
        opacity: 0;
        transform: scale(0.96);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-hdr {
    padding: 12px 16px 12px 18px;
    padding-right: 36px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.modal-title-text {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
}

.modal-x {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: #e2e8f0;
    border: none;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.modal-x:hover {
    background: #fee2e2;
    color: #dc2626;
}

.modal-body {
    padding: 16px 18px;
    overflow-y: auto;
    font-size: 13px;
    color: #334155;
}

.modal-footer {
    padding: 10px 18px 14px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: #fafafa;
}

/* 按鈕風格 */
.mbtn-sec {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

.mbtn-sec:hover {
    background: #e2e8f0;
}

.mbtn-pri {
    background: #0f766e;
    border: none;
    color: white;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(15, 118, 110, 0.2);
}

.mbtn-pri:hover {
    background: #0d655e;
}

.mbtn-danger {
    background: #dc2626;
    border: none;
    color: white;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(220, 38, 38, 0.2);
}

.mbtn-danger:hover {
    background: #b91c1c;
}

.mbtn-offsign {
    background: #0f766e;
    border: none;
    color: white;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(15, 118, 110, 0.2);
}

.mbtn-offsign:hover:not(:disabled) {
    background: #115e59;
}

.mbtn-offsign:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
}

/* 病患資訊膠囊 */
.pt-info-pill {
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
    color: #0f766e;
    margin-bottom: 12px;
}

.field-hint {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 12px;
}

/* 血壓輸入彈窗排版 */
.bp-modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.bp-modal-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.bp-modal-lbl {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.bp-modal-input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    background: white;
    padding: 0 8px;
    box-sizing: border-box;
    transition: all 0.15s ease;
}

.bp-modal-input-wrap:focus-within {
    border-color: #0f766e;
    box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
}

.bp-modal-input {
    width: 100%;
    height: 36px;
    border: none;
    outline: none;
    font-size: 16px;
    font-weight: 700;
    color: #0f766e;
    text-align: center;
}

.bp-modal-unit {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 600;
}

.bp-preview-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 12.5px;
    color: #475569;
    text-align: center;
}

.warning-callout {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 12px;
    color: #92400e;
    line-height: 1.45;
}

.success-callout {
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 12.5px;
    color: #166534;
    line-height: 1.45;
}

/* 體重摘要看板 */
.wt-summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 8px;
}

.wt-cell {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 7px 10px;
    display: flex;
    flex-direction: column;
}

.wt-cell.highlight {
    background: #f0fdfa;
    border-color: #99f6e4;
}

.wt-cell.full-width {
    grid-column: span 2;
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.wt-lbl {
    font-size: 10.5px;
    color: #64748b;
    font-weight: 600;
}

.wt-val {
    font-size: 14.5px;
    font-weight: 700;
    color: #1e293b;
    margin-top: 2px;
}

/* 核對條目 */
.checklist-section {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sec-subtitle {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 2px;
}

.chk-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 7px 10px;
    cursor: pointer;
    font-size: 12.5px;
    user-select: none;
    transition: all 0.15s ease;
}

.chk-item-row:hover {
    background: #f1f5f9;
}

.chk-box-sq {
    width: 17px;
    height: 17px;
    border: 1.5px solid #cbd5e1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: white;
    font-weight: 700;
    flex-shrink: 0;
    background: white;
}

.chk-box-sq.on {
    background: #0f766e;
    border-color: #0f766e;
}

.chk-item-label {
    flex: 1;
    color: #334155;
    font-weight: 500;
}

/* 凝血 radio 表單 */
.form-group-block {
    margin-bottom: 12px;
}

.form-sec-lbl {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
    display: block;
}

.radio-options-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 6px;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12.5px;
    cursor: pointer;
    color: #1e293b;
}

.sub-input-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    padding-left: 10px;
}

.sub-input-lbl {
    font-size: 11.5px;
    color: #64748b;
    flex-shrink: 0;
}

.sub-text-input {
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 5px 8px;
    font-size: 12.5px;
    outline: none;
    flex: 1;
    box-sizing: border-box;
}

.sub-text-input:focus {
    border-color: #0f766e;
}

.sub-textarea {
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 6px 8px;
    font-size: 12.5px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    resize: none;
}

.sub-textarea:focus {
    border-color: #dc2626;
}

/* 消耗醫材列表 */
.supply-checklist-box {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 12px;
}

.supply-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12.5px;
}

.supply-item-row:hover {
    background: #f1f5f9;
}

.supply-item-name {
    flex: 1;
    font-weight: 500;
    color: #1e293b;
}

.supply-item-qty {
    font-weight: 700;
    color: #0f766e;
}

.signer-banner {
    background: #f1f5f9;
    border-radius: 6px;
    padding: 7px 10px;
    font-size: 12px;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* 給藥條目卡片 */
.drug-banner {
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    border-radius: 8px;
    padding: 9px 12px;
    margin-bottom: 12px;
}

.drug-name-lg {
    font-size: 14.5px;
    font-weight: 700;
    color: #7c3aed;
}

.drug-meta-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

.form-row-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 8px;
}

.form-col {
    display: flex;
    flex-direction: column;
}

/* Off-Sign 總大核對清單表格 */
.off-check-table {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.off-check-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    font-size: 12.5px;
    background: white;
}

.off-check-row.ok {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.off-check-row.fail {
    background: #fffbeb;
    border-color: #fde68a;
    cursor: pointer;
}

.off-check-row.fail:hover {
    background: #fef3c7;
}

.off-check-icon {
    font-size: 13px;
    font-weight: 700;
    width: 20px;
    text-align: center;
}

.off-check-row.ok .off-check-icon {
    color: #16a34a;
}

.off-check-label {
    font-weight: 600;
    color: #334155;
    width: 140px;
    flex-shrink: 0;
}

.off-check-val {
    flex: 1;
    color: #475569;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.off-check-hint {
    font-size: 11px;
    color: #b45309;
    font-weight: 700;
    flex-shrink: 0;
}

.audit-notice-card {
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 11.5px;
}

.audit-title {
    font-weight: 700;
    color: #115e59;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.audit-text {
    color: #134e4a;
    line-height: 1.45;
}
</style>

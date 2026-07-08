<template>
    <div class="kpi-strip">
        <div class="kpi-card warn" style="min-width: 135px">
            <div class="kc-label">HCT 比對</div>
            <div class="hct-sub-grid">
                <div class="hct-box">
                    <span class="hl">LW</span>
                    <span class="hv">{{
                        currentPatient.kpi?.hctLw || "—"
                    }}</span>
                </div>
                <div class="hct-divider">/</div>
                <div class="hct-box">
                    <span class="hl" style="color: var(--amber)">TW</span>
                    <span class="hv" style="color: var(--amber)">{{
                        currentPatient.kpi?.hctTw || "—"
                    }}</span>
                </div>
                <div class="hct-divider">/</div>
                <div class="hct-box">
                    <span class="hl">AT</span>
                    <span class="hv" style="color: var(--slate-lt)">—</span>
                </div>
            </div>
        </div>

        <div class="kpi-card ok">
            <div class="kc-label">Hb(M)</div>
            <div class="kc-val">{{ currentPatient.kpi?.hb || "—" }}</div>
            <div class="kc-sub">g/dL 月</div>
        </div>

        <div class="kpi-card ok">
            <div class="kc-label">Ferritin(Q)</div>
            <div class="kc-val">{{ currentPatient.kpi?.ferritin || "—" }}</div>
            <div class="kc-sub">ng/mL 季</div>
        </div>

        <div class="kpi-card warn">
            <div class="kc-label">Kt/V</div>
            <div class="kc-val">{{ currentPatient.kpi?.ktv || "—" }}</div>
            <div class="kc-sub">Daugirdas</div>
        </div>

        <div class="kpi-card ok">
            <div class="kc-label">URR</div>
            <div class="kc-val">{{ currentPatient.kpi?.urr || "—" }}</div>
            <div class="kc-sub">%</div>
        </div>

        <div class="kpi-card ok">
            <div class="kc-label">Ca × P</div>
            <div class="kc-val">{{ currentPatient.kpi?.cap || "—" }}</div>
            <div class="kc-sub">mg²/dL²</div>
        </div>

        <div class="kpi-card clickable" @click="handleDryWeightEdit">
            <div class="kc-label">現行乾體重 ✏️</div>
            <div class="kc-val">{{ currentPatient.kpi?.dryWeight || "—" }}</div>
            <div class="kc-sub">kg (下回生效)</div>
        </div>

        <div class="kpi-card clickable">
            <div class="kc-label">床邊 F/S</div>
            <div class="kc-val">{{ currentPatient.kpi?.fs || "—" }}</div>
            <div class="kc-sub">mg/dL</div>
        </div>

        <div class="kpi-card nursing-summary-card">
            <div class="kc-label"><i class="ti ti-notes"></i> 今日護理摘要</div>
            <div class="nursing-body" v-html="formattedNursingText"></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useDoctorStore } from "@/store/doctorStore";

const store = useDoctorStore();
const currentPatient = computed(() => store.currentPatient);

// 格式化護理摘要的換行符號
const formattedNursingText = computed(() => {
    if (!currentPatient.value?.nursingSummary)
        return '<span style="color:var(--slate-lt)">暫無今日護理註記</span>';
    return currentPatient.value.nursingSummary.replace(/\n/g, "<br>");
});

// 觸發乾體重異動編輯
const handleDryWeightEdit = () => {
    console.log(`✏️ 點擊異動病患 [${currentPatient.value.name}] 的乾體重設定`);
    // 後續第四階段將在此觸發全域的彈窗 (Modal-DryWeight)
};
</script>

<style scoped>
/* 🎨 100% 完美對齊 V39 HTML 樣式表與 CSS 變數環境 🎨 */
.kpi-strip {
    display: flex;
    gap: 5px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
    flex-wrap: nowrap;
    width: 100%;
}

/* 隱藏滾動條，維持介面精緻俐落 */
.kpi-strip::-webkit-scrollbar {
    height: 4px;
}
.kpi-strip::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 2px;
}

.kpi-card {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 5px 10px;
    text-align: center;
    flex-shrink: 0;
    min-width: 72px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.kpi-card .kc-label {
    font-size: 9px;
    color: var(--slate-lt);
    font-weight: 700;
    margin-bottom: 2px;
}

.kpi-card .kc-val {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.kpi-card .kc-sub {
    font-size: 9px;
    color: var(--slate-lt);
    margin-top: 1px;
}

/* 正常範圍綠色調 */
.kpi-card.ok {
    border-color: var(--green-bd);
    background: var(--green-lt);
}
.kpi-card.ok .kc-val {
    color: var(--green);
}

/* 警示邊界琥珀色調 */
.kpi-card.warn {
    border-color: var(--amber-bd);
    background: var(--amber-lt);
}
.kpi-card.warn .kc-val {
    color: var(--amber);
}

/* 具備可互動屬性的醫囑編輯卡片藍色調 */
.kpi-card.clickable {
    cursor: pointer;
    border-color: var(--pri-bd);
    background: var(--pri-lt);
    transition: all 0.15s ease;
}
.kpi-card.clickable:hover {
    border-color: var(--pri2);
    transform: translateY(-1px);
}
.kpi-card.clickable .kc-label {
    color: var(--pri);
}
.kpi-card.clickable .kc-val {
    color: var(--pri);
}

/* 📋 護理快顯專屬樣式架構 */
.kpi-card.nursing-summary-card {
    min-width: 190px;
    text-align: left;
    background: var(--white);
    border-color: var(--border);
}
.kpi-card.nursing-summary-card .kc-label {
    color: var(--slate);
}
.nursing-body {
    font-size: 11px;
    color: #334155;
    line-height: 1.4;
    margin-top: 2px;
    font-weight: 500;
}

/* 🩸 HCT 三段式盒布局樣式還原 */
.hct-sub-grid {
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
    margin-top: 2px;
}
.hct-box {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.hct-box .hl {
    font-size: 8px;
    color: var(--slate-lt);
    font-weight: 600;
}
.hct-box .hv {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
}
.hct-divider {
    color: var(--slate-lt);
    font-size: 11px;
    align-self: flex-end;
    margin-bottom: 1px;
}
</style>

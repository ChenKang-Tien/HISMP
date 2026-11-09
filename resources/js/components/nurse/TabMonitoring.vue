<template>
    <div class="monitoring-grid-container">
        <div class="mon-wrap-hdr">
            <span><i class="ti ti-cpu"></i> 透析機數據：透析開始後每小時自動取得一次 ・ 長按格子可人工修改</span>
            <button class="btn-add-meas" @click="openExtraModal">＋ 加測</button>
        </div>

        <div class="mon-wrap">
            <table class="mon-t">
                <thead>
                    <tr>
                        <th class="time-hdr">時間</th>
                        <th>收縮壓</th>
                        <th>舒張壓</th>
                        <th>脈搏</th>
                        <th>血流速</th>
                        <th>靜脈壓</th>
                        <th>TMP/DP</th>
                        <th>已脫水量</th>
                        <th>脫水速率</th>
                        <th>Heparin</th>
                        <th>透液流量</th>
                        <th>傳導度</th>
                        <th>透液溫度</th>
                        <th>處置與臨床備註</th>
                        <th>機台評估</th>
                        <th class="care-hdr">Care Sign</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in baseGridRows" :key="row.key" :class="{'current-row': row.isCurrent, 'extra-row': row.isExtra}">
                        <td class="time-cell" :class="{ extra: row.isExtra }">
                            {{ row.label }}<br /><span class="time-sub">{{ row.time }}</span>
                        </td>
                        <td v-for="field in fields" :key="field.id" class="data-cell" @dblclick="triggerModify(row.key, field.id, row[field.id])">
                            <span :class="['dv', { 'empty-val': !row[field.id], 'modified-val': row.isModified && row.isModified[field.id] }]" :title="modifiedTitle(row, field.id)">{{ row[field.id] || "—" }}</span>
                        </td>
                        <td class="fkr-cell-text">
                            <div v-if="store.careSignData?.[row.key]">
                                <div>AK:{{ store.careSignData[row.key]?.ak }}</div>
                                <div>滲:{{ store.careSignData[row.key]?.needle }}</div>
                                <div>管:{{ store.careSignData[row.key]?.tube }}</div>
                            </div>
                            <span v-else class="empty-dash">—</span>
                        </td>
                        <td class="care-cell" :class="{ signed: store.careSignData?.[row.key] }" @click="toggleCareSign(row.key, row.label)">
                            <button class="care-btn" :class="{ signed: store.careSignData?.[row.key] }">{{ store.careSignData?.[row.key] ? "✓" : "" }}</button>
                            <div v-if="store.careSignData?.[row.key]" class="care-who">楚心瑜</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 加測 Modal -->
        <div :class="['modal-overlay', { open: showExtraModal }]">
            <div class="modal">
                <div class="modal-hdr"><i class="ti ti-plus"></i> 加測記錄</div>
                <div class="form-group">
                    <label>時間</label>
                    <input v-model="extraFormData.time" type="time" class="form-input" />
                </div>
                <div class="form-grid">
                    <div v-for="field in fields" :key="field.id" class="form-group">
                        <label>{{ field.label }}</label>
                        <input v-model="extraFormData[field.id]" class="form-input" :placeholder="field.label" />
                    </div>
                </div>
                <div class="mbtn-row">
                    <button class="mbtn sec" @click="showExtraModal = false">取消</button>
                    <button class="mbtn pri" @click="saveExtraRow">確認</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, reactive } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();
const showExtraModal = ref(false);
const fields = [
    { id: "sys", label: "收縮壓" }, { id: "dia", label: "舒張壓" }, { id: "pr", label: "脈搏" }, { id: "qb", label: "血流速" },
    { id: "vp", label: "靜脈壓" }, { id: "tmp", label: "TMP/DP" }, { id: "uf", label: "已脫水量" },
    { id: "ufr", label: "脫水速率" }, { id: "hep", label: "Heparin" }, { id: "qd", label: "透液流量" },
    { id: "cond", label: "傳導度" }, { id: "temp", label: "透液溫度" }
];
const extraFormData = reactive({});

const baseGridRows = ref([]);

// 前端欄位 id -> 後端資料庫欄位
const dbFieldMap = {
    sys: "BDPS", dia: "BDPD", pr: "BDPL", qb: "BLDF", vp: "VEPS", tmp: "TMP",
    uf: "UF", ufr: "UFRA", hep: "HPCV", qd: "DLFL", cond: "CDCT", temp: "DLTP",
};
// 由後端的 is_manual_override / original_values 轉成前端欄位 id 為 key
const buildModifyInfo = (remote) => {
    const isModified = {};
    const original = {};
    if (remote?.is_manual_override) {
        Object.entries(dbFieldMap).forEach(([id, db]) => {
            if (remote.is_manual_override[db]) {
                isModified[id] = true;
                const o = remote.original_values?.[db];
                original[id] = o === null || o === undefined || o === "" ? "—" : o;
            }
        });
    }
    // 修改歷程：依前端欄位 id 分組
    const history = {};
    const idOf = Object.fromEntries(Object.entries(dbFieldMap).map(([id, db]) => [db, id]));
    (remote?.edit_history || []).forEach((h) => {
        const id = idOf[h.field];
        if (!id) return;
        (history[id] ||= []).push(h);
    });
    return { isModified, original, history };
};
const modifiedTitle = (row, fieldId) => {
    if (!row.isModified?.[fieldId]) return "";
    const show = (v) => (v === null || v === undefined || v === "" ? "—" : v);
    const lines = [`已人工修改（原始資料：${row.original?.[fieldId] ?? "—"}）`];
    (row.history?.[fieldId] || []).forEach((h) => {
        lines.push(`${h.at}  ${h.nurse}：${show(h.old)} → ${show(h.new)}`);
    });
    return lines.join("\n");
};

const syncRows = () => {
    if (!store.monitoringData) return;
    const order = ["pre", "h1", "h2", "h3", "h4", "post1", "post2"];
    const labelMap = { pre: "透前", h1: "第1小時", h2: "第2小時", h3: "第3小時", h4: "第4小時", post1: "透後臥", post2: "透後坐" };

    const rows = order.map((key) => {
        const remote = store.monitoringData[key];
        // 比對機台原始資料 (remote) 與手動修正值
        const { isModified, original, history } = buildModifyInfo(remote);

        return {
            key: key,
            label: labelMap[key] || key,
            time: remote?.HCDTTM ? remote.HCDTTM.split(" ")[1].substring(0, 5) : "—",
            sys: remote?.systolic_blood_pressure || remote?.BDPS || "—",
            dia: remote?.diastolic_blood_pressure || remote?.BDPD || "—",
            pr: remote?.P || remote?.BDPL || "—",
            qb: remote?.BLDF || "—",
            vp: remote?.VEPS || "—",
            tmp: remote?.TMP || "—",
            uf: remote?.UF !== undefined ? remote.UF : "—",
            ufr: remote?.UFRA !== undefined ? remote.UFRA : "—",
            hep: remote?.HPCV !== undefined ? remote.HPCV : "—",
            qd: remote?.DLFL || "—",
            cond: remote?.CDCT !== undefined ? remote.CDCT : "—",
            temp: remote?.DLTP || "—",
            isExtra: false,
            // 標記修改狀態 (檢查是否有手動覆蓋) 與原始機台值
            isModified,
            original,
            history,
        };
    });

    Object.keys(store.monitoringData).forEach(key => {
        if (key.startsWith('manual_')) {
            const remote = store.monitoringData[key];
            rows.push({
                key: key,
                label: "⚡ 加測",
                time: remote.HCDTTM ? remote.HCDTTM.split(" ")[1].substring(0, 5) : "—",
                sys: remote.BDPS || "—",
                dia: remote.BDPD || "—",
                pr: remote.BDPL || "—",
                qb: remote.BLDF || "—",
                vp: remote.VEPS || "—",
                tmp: remote.TMP || "—",
                uf: remote.UF !== undefined ? remote.UF : "—",
                ufr: remote.UFRA !== undefined ? remote.UFRA : "—",
                hep: remote.HPCV !== undefined ? remote.HPCV : "—",
                qd: remote.DLFL || "—",
                cond: remote.CDCT !== undefined ? remote.CDCT : "—",
                temp: remote.DLTP || "—",
                ...buildModifyInfo(remote),
                isExtra: true
            });
        }
    });

    baseGridRows.value = rows.sort((a, b) => {
        const getPriority = (row) => {
            if (row.key === 'pre') return -1;
            if (row.key === 'post1') return 999998;
            if (row.key === 'post2') return 999999;
            return parseInt(row.time.replace(':', '')) || 0;
        };
        return getPriority(a) - getPriority(b);
    });
};

watch(() => store.monitoringData, syncRows, { deep: true, immediate: true });

const openExtraModal = () => {
    console.log("Current check_id for testing:", store.currentPatient.id);
    fields.forEach(f => extraFormData[f.id] = "");
    extraFormData.time = new Date().toLocaleTimeString('zh-TW', { hour: '2-digit', minute: '2-digit', hour12: false });
    showExtraModal.value = true;
};

const saveExtraRow = async () => {
    try {
        await store.api.post(`/dialysis-checks/${store.currentPatient.id}/manual-records`, extraFormData);
        // 重新從後端拉取數據以確保同步
        store.monitoringData = await store.fetchMonitoringData(store.currentPatient.id);
        showExtraModal.value = false;
    } catch (err) {
        console.error("🔴 [Add Manual Record Error]:", err);
        alert("儲存加測失敗");
    }
};

const triggerModify = async (key, f, currentVal) => {
    // 若為血壓相關欄位，增加明確的提示文字
    let promptText = `修改參數 [${f}]：`;
    if (f === 'sys') promptText = '修改收縮壓 (Sys)：';
    if (f === 'dia') promptText = '修改舒張壓 (Dia)：';
    
    const nv = prompt(promptText, currentVal === "—" ? "" : currentVal);
    if (nv !== null) {
        try {
            const row = baseGridRows.value.find((r) => r.key === key);
            if (!row) return;

            // 呼叫 API 更新
            await store.api.put(`/dialysis-checks/${store.currentPatient.id}/monitoring-data`, {
                key: key,
                field: f,
                value: nv === "" ? null : nv,
                old_value: currentVal === "—" ? null : currentVal, // 修改前畫面上的值，供稽核紀錄
                time: row.time // 傳遞格子上的時間點
            });

            // 更新本地數據與標記
            // 重新拉取，取得後端合併後的修改標記與原始機台值
            store.monitoringData = await store.fetchMonitoringData(store.currentPatient.id);

        } catch (err) {
            console.error("🔴 [Update Monitoring Error]:", err);
            alert("修改失敗");
        }
    }
};

const toggleCareSign = (key, label) => {
    if (store.careSignData[key]) {
        delete store.careSignData[key];
    } else {
        store.careSignData[key] = { ak: "Clear", needle: "無", tube: "有" };
    }
};
</script>

<style scoped>
.mon-wrap-hdr { background: #f0fdfa; border-bottom: 1px solid #99f6e4; padding: 4px 10px; font-size: 10px; color: #0f766e; display: flex; align-items: center; justify-content: space-between; gap: 4px; }
.btn-add-meas { font-size: 10px; background: #b45309; color: white; border: none; border-radius: 5px; padding: 3px 8px; cursor: pointer; }
.mon-wrap { overflow: auto; width: 100%; }
.mon-t { border-collapse: collapse; min-width: 100%; white-space: nowrap; font-size: 11px; }
.mon-t th { background: #134e4a; color: white; padding: 5px 7px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.15); position: sticky; top: 0; z-index: 10; font-size: 10px; font-weight: 700; }
.mon-t td { padding: 4px 6px; border: 1px solid #e2e8f0; text-align: center; vertical-align: middle; }
.mon-t td.time-cell { background: #f0fdfa; color: #0f766e; font-weight: 700; font-size: 10px; text-align: left; position: sticky; left: 0; z-index: 5; border-right: 2px solid #99f6e4; padding: 4px 8px; }
.modified-val { background-color: #fef08a !important; color: #854d0e !important; cursor: help; border-bottom: 2px dotted #ca8a04; }
.modified-val::after { content: '✎'; font-size: 9px; margin-left: 2px; vertical-align: super; }
.empty-val { border-bottom: none !important; }
.dv { font-size: 11px; font-weight: 600; color: #1e293b; border-bottom: 1px solid transparent; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: white; padding: 20px; border-radius: 8px; width: 400px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
.form-input { width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
.mbtn-row { display: flex; justify-content: flex-end; gap: 10px; }
.mbtn { padding: 5px 15px; cursor: pointer; border-radius: 4px; border: none; }
.mbtn.pri { background: #0f766e; color: white; }
.mbtn.sec { background: #e2e8f0; }
</style>

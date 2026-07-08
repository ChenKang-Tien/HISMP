<template>
    <div class="monitoring-grid-container">
        <div class="mon-wrap-hdr">
            <span
                ><i class="ti ti-cpu"></i>
                透析機數據：透析開始後每小時自動取得一次 ・
                長按格子可人工修改</span
            >
            <button class="btn-add-meas" @click="openExtraModal">
                ＋ 加測
            </button>
        </div>

        <div class="mon-wrap">
            <table class="mon-t">
                <thead>
                    <tr>
                        <th class="time-hdr">時間</th>
                        <th>血壓</th>
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
                    <tr
                        v-for="row in baseGridRows"
                        :key="row.key"
                        :class="{
                            'current-row': row.isCurrent,
                            'extra-row': row.isExtra,
                        }"
                    >
                        <td class="time-cell" :class="{ extra: row.isExtra }">
                            {{ row.label }}<br /><span class="time-sub">{{
                                row.time
                            }}</span>
                        </td>

                        <!-- 數據格 (點擊看圖表、長按修改) -->
                        <td
                            v-for="field in fields"
                            :key="field"
                            class="data-cell"
                            @dblclick="
                                triggerModify(row.key, field, row[field])
                            "
                        >
                            <div
                                v-if="hasModification(row.key, field)"
                                class="strike-box"
                            >
                                <span class="old-val">{{
                                    getOldVal(row.key, field)
                                }}</span>
                                <span class="dv text-amber font-bold">{{
                                    row[field]
                                }}</span>
                            </div>
                            <span
                                v-else
                                class="dv"
                                :class="{
                                    crit:
                                        field === 'bp' &&
                                        row[field] === '92/54',
                                }"
                            >
                                {{ row[field] || "—" }}
                            </span>
                        </td>

                        <!-- 處置與備註 -->
                        <td>
                            <input
                                v-model="store.monNotes[row.key]"
                                type="text"
                                class="inline-note-input"
                                placeholder="處置..."
                            />
                        </td>

                        <!-- 機台核對狀態 (AK/滲血/管路) -->
                        <td class="fkr-cell-text">
                            <div v-if="store.careSignData[row.key]">
                                <div>
                                    AK:{{ store.careSignData[row.key].ak }}
                                </div>
                                <div>
                                    滲:{{ store.careSignData[row.key].needle }}
                                </div>
                                <div>
                                    管:{{ store.careSignData[row.key].tube }}
                                </div>
                            </div>
                            <span v-else class="empty-dash">—</span>
                        </td>

                        <!-- Care Sign 簽章欄 (完全對齊原稿固定右側) -->
                        <td
                            class="care-cell"
                            :class="{ signed: store.careSignData[row.key] }"
                            @click="toggleCareSign(row.key, row.label)"
                        >
                            <button
                                class="care-btn"
                                :class="{ signed: store.careSignData[row.key] }"
                            >
                                {{ store.careSignData[row.key] ? "✓" : "" }}
                            </button>
                            <div
                                v-if="store.careSignData[row.key]"
                                class="care-who"
                            >
                                楚心瑜
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 局部加測 Modal -->
        <div :class="['modal-overlay', { open: showExtraModal }]">
            <div class="modal">
                <div class="modal-hdr"><i class="ti ti-plus"></i> 加測記錄</div>
                <div class="form-group">
                    <label class="form-label">加測時間 (HH:MM)</label>
                    <input
                        v-model="extraTimeInput"
                        type="time"
                        class="form-input"
                    />
                </div>
                <div class="mbtn-row">
                    <button class="mbtn sec" @click="showExtraModal = false">
                        取消
                    </button>
                    <button class="mbtn pri" @click="saveExtraRow">
                        確認加入
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();
const showExtraModal = ref(false);
const extraTimeInput = ref("10:42");

const fields = [
    "bp",
    "pr",
    "qb",
    "vp",
    "tmp",
    "uf",
    "ufr",
    "hep",
    "qd",
    "cond",
    "temp",
];

const baseGridRows = ref([
    {
        key: "pre",
        label: "透前",
        time: "09:05",
        bp: "148/90",
        pr: "72",
        qb: "—",
        vp: "—",
        tmp: "—",
        uf: "—",
        ufr: "—",
        hep: "0.5/6.0",
        qd: "—",
        cond: "—",
        temp: "—",
        isExtra: false,
    },
    {
        key: "h1",
        label: "第1小時",
        time: "10:00",
        bp: "142/86",
        pr: "74",
        qb: "250",
        vp: "140",
        tmp: "100",
        uf: "0.88",
        ufr: "0.88",
        hep: "5.5",
        qd: "500",
        cond: "140",
        temp: "35.5",
        isExtra: false,
    },
    {
        key: "x1",
        label: "⚡ 加測",
        time: "10:42",
        bp: "92/54",
        pr: "90",
        qb: "—",
        vp: "—",
        tmp: "—",
        uf: "—",
        ufr: "—",
        hep: "—",
        qd: "—",
        cond: "—",
        temp: "—",
        isExtra: true,
    },
    {
        key: "h2",
        label: "第2小時",
        time: "11:00",
        bp: "138/84",
        pr: "76",
        qb: "250",
        vp: "145",
        tmp: "100",
        uf: "1.75",
        ufr: "0.88",
        hep: "5.0",
        qd: "500",
        cond: "140",
        temp: "35.5",
        isCurrent: true,
        isExtra: false,
    },
    {
        key: "h3",
        label: "第3小時",
        time: "12:00",
        bp: "—",
        pr: "—",
        qb: "—",
        vp: "—",
        tmp: "—",
        uf: "—",
        ufr: "—",
        hep: "—",
        qd: "—",
        cond: "—",
        temp: "—",
        isExtra: false,
    },
    {
        key: "h4",
        label: "第4小時",
        time: "13:00",
        bp: "—",
        pr: "—",
        qb: "—",
        vp: "—",
        tmp: "—",
        uf: "—",
        ufr: "—",
        hep: "—",
        qd: "—",
        cond: "—",
        temp: "—",
        isExtra: false,
    },
    {
        key: "post1",
        label: "透後臥",
        time: "待填",
        bp: "—",
        pr: "—",
        qb: "—",
        vp: "—",
        tmp: "—",
        uf: "—",
        ufr: "—",
        hep: "End",
        qd: "—",
        cond: "—",
        temp: "—",
        isExtra: false,
    },
    {
        key: "post2",
        label: "透後坐",
        time: "待填",
        bp: "—",
        pr: "—",
        qb: "—",
        vp: "—",
        tmp: "—",
        uf: "—",
        ufr: "—",
        hep: "—",
        qd: "—",
        cond: "—",
        temp: "—",
        isExtra: false,
    },
]);

const historyModPool = ref({});

const hasModification = (key, f) => !!historyModPool.value[`${key}_${f}`];
const getOldVal = (key, f) => historyModPool.value[`${key}_${f}`] || "";

const triggerModify = (key, f, currentVal) => {
    const nv = prompt(
        `修改參數 [${f.toUpperCase()}]（原值：${currentVal}）：`,
        currentVal,
    );
    if (nv && nv !== currentVal) {
        historyModPool.value[`${key}_${f}`] = currentVal;
        const row = baseGridRows.value.find((r) => r.key === key);
        if (row) row[f] = nv;
    }
};

const openExtraModal = () => {
    extraTimeInput.value = "10:42";
    showExtraModal.value = true;
};

const saveExtraRow = () => {
    baseGridRows.value.splice(2, 0, {
        key: `custom_${Date.now()}`,
        label: "⚡ 加測",
        time: extraTimeInput.value,
        bp: "106/62",
        pr: "78",
        qb: "250",
        vp: "120",
        tmp: "95",
        uf: "1.10",
        ufr: "0.88",
        hep: "5.5",
        qd: "500",
        cond: "140",
        temp: "35.5",
        isExtra: true,
    });
    showExtraModal.value = false;
};

const toggleCareSign = (key, label) => {
    if (store.careSignData[key]) {
        delete store.careSignData[key];
    } else {
        store.careSignData[key] = { ak: "Clear", needle: "無", tube: "有" };
        store.addNursingRecord(
            `Care Sign（${label}）核對完成：AK Clear，針孔無滲血，管路固定良好。`,
        );
    }
};
</script>

<style scoped>
.mon-wrap-hdr {
    background: #f0fdfa;
    border-bottom: 1px solid #99f6e4;
    padding: 4px 10px;
    font-size: 10px;
    color: #0f766e;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
}
.btn-add-meas {
    font-size: 10px;
    background: #b45309;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 3px 8px;
    cursor: pointer;
}
.mon-wrap {
    overflow: auto;
    width: 100%;
}
.mon-t {
    border-collapse: collapse;
    min-width: 100%;
    white-space: nowrap;
    font-size: 11px;
}
.mon-t th {
    background: #134e4a;
    color: white;
    padding: 5px 7px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.15);
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 10px;
    font-weight: 700;
}
.mon-t th.time-hdr {
    text-align: left;
    position: sticky;
    left: 0;
    z-index: 20;
    min-width: 82px;
    background: #134e4a;
}
.mon-t th.care-hdr {
    position: sticky;
    right: 0;
    z-index: 20;
    background: #f0fdfa;
    color: #0f766e;
    border-left: 2px solid #86efac;
    min-width: 62px;
}
.mon-t td {
    padding: 4px 6px;
    border: 1px solid #e2e8f0;
    text-align: center;
    vertical-align: middle;
}
.mon-t td.time-cell {
    background: #f0fdfa;
    color: #0f766e;
    font-weight: 700;
    font-size: 10px;
    text-align: left;
    position: sticky;
    left: 0;
    z-index: 5;
    border-right: 2px solid #99f6e4;
    padding: 4px 8px;
}
.mon-t td.time-cell.extra {
    background: #fef3c7;
    color: #b45309;
}
.mon-t tr:hover td:not(.time-cell):not(.care-cell) {
    background: #f0fdfa;
}
.mon-t tr.current-row td:not(.time-cell):not(.care-cell) {
    background: #f0fdf8;
}
.mon-t tr.extra-row td:not(.time-cell):not(.care-cell) {
    background: #fffbeb;
}

.data-cell {
    padding: 4px 5px;
    cursor: pointer;
}
.dv {
    font-size: 11px;
    font-weight: 600;
    color: #1e293b;
}
.dv.crit {
    color: #b91c1c;
    font-weight: 700;
    background: #fef2f2;
    padding: 2px 4px;
    border-radius: 4px;
}
.strike-box {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}
.old-val {
    font-size: 9px;
    color: #94a3b8;
    text-decoration: line-through;
    font-style: italic;
}
.fkr-cell-text {
    font-size: 9px;
    color: #64748b;
    line-height: 1.2;
    text-align: left;
    padding-left: 4px;
}
.empty-dash {
    color: #cbd5e1;
    display: block;
    text-align: center;
}

.care-cell {
    padding: 3px 4px;
    text-align: center;
    position: sticky;
    right: 0;
    z-index: 5;
    border-left: 2px solid #86efac;
    background: white;
    cursor: pointer;
}
.care-cell.signed {
    background: #f0fdf4;
}
.care-btn {
    width: 26px;
    height: 20px;
    border-radius: 4px;
    border: 1.5px solid #e2e8f0;
    background: white;
    font-size: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.care-btn.signed {
    background: #f0fdf4;
    border-color: #86efac;
    color: #15803d;
}
.care-who {
    font-size: 8px;
    color: #15803d;
    margin-top: 1px;
}

.inline-note-input {
    width: 100%;
    border: 1px solid transparent;
    background: transparent;
    font-size: 11px;
    padding: 2px;
}
.inline-note-input:focus {
    border-color: #cbd5e1;
    background: white;
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
    max-width: 340px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
}
.modal-hdr {
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 10px;
}
.form-group {
    margin-bottom: 12px;
}
.form-label {
    font-size: 11px;
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
}
.form-input {
    width: 100%;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 9px;
    font-size: 12px;
}
.mbtn-row {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}
.mbtn {
    border: none;
    border-radius: 6px;
    padding: 7px 14px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.mbtn.sec {
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #cbd5e1;
}
.mbtn.pri {
    background: #0f766e;
    color: white;
}
</style>

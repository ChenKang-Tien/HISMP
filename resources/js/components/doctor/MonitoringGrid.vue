<template>
    <div class="grid-section" v-if="store.currentPatient">
        <div class="grid-wrap">
            <table class="monitoring-table">
                <thead>
                    <tr>
                        <th class="time-col">時間</th>
                        <th @click="store.showChart('血壓 BP')">
                            血壓 BP<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mmHg</span
                            >
                        </th>
                        <th @click="store.showChart('脈搏')">
                            脈搏<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >次/min</span
                            >
                        </th>
                        <th @click="store.showChart('血流速 Qb')">
                            血流速 Qb<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mL/min</span
                            >
                        </th>
                        <th @click="store.showChart('靜脈壓 VP')">
                            靜脈壓 VP<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mmHg</span
                            >
                        </th>
                        <th @click="store.showChart('TMP/DP')">
                            TMP/DP<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mmHg</span
                            >
                        </th>
                        <th @click="store.showChart('已脫水量')">
                            已脫水量<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >L</span
                            >
                        </th>
                        <th @click="store.showChart('脫水速率 UFR')">
                            脫水速率 UFR<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >L/hr</span
                            >
                        </th>
                        <th @click="store.showChart('Heparin')">
                            Heparin<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mL/hr</span
                            >
                        </th>
                        <th @click="store.showChart('透液流量 Qd')">
                            透液流量 Qd<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mL/min</span
                            >
                        </th>
                        <th @click="store.showChart('傳導度')">
                            傳導度<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >mS/cm</span
                            >
                        </th>
                        <th @click="store.showChart('透液溫度')">
                            透液溫度<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >°C</span
                            >
                        </th>
                        <th>處置備註</th>
                        <th style="min-width: 80px">
                            AK/針滲<br /><span
                                style="font-weight: 400; font-size: 9px"
                                >管固/N/S</span
                            >
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, idx) in store.currentPatient
                            .monitoringRecords || []"
                        :key="idx"
                        :class="{ 'current-row': row.isCurrentRow }"
                    >
                        <td class="time-cell" :class="{ add: row.isAdd }">
                            <span v-html="row.label"></span><br /><span
                                style="font-size: 9px; font-weight: 400"
                                >{{ row.time }}</span
                            >
                        </td>
                        <td
                            class="bp"
                            :class="{ warn: row.isWarn }"
                            @click="store.showChart('血壓 BP')"
                        >
                            {{ row.bp }}
                        </td>
                        <td
                            :class="{ warn: row.isWarn }"
                            @click="store.showChart('脈搏')"
                        >
                            {{ row.pr }}
                        </td>
                        <td :class="{ empty: row.qb === '—' }">{{ row.qb }}</td>
                        <td :class="{ empty: row.vp === '—' }">{{ row.vp }}</td>
                        <td :class="{ empty: row.tmp === '—' }">
                            {{ row.tmp }}
                        </td>
                        <td :class="{ empty: row.uf === '—' }">{{ row.uf }}</td>
                        <td :class="{ empty: row.ufr === '—' }">
                            {{ row.ufr }}
                        </td>
                        <td>
                            <span
                                :class="{
                                    'hep-cell': row.hep.includes('設定'),
                                }"
                                >{{ row.hep }}</span
                            >
                        </td>
                        <td :class="{ empty: row.qd === '—' }">{{ row.qd }}</td>
                        <td :class="{ empty: row.cond === '—' }">
                            {{ row.cond }}
                        </td>
                        <td :class="{ empty: row.temp === '—' }">
                            {{ row.temp }}
                        </td>
                        <td class="disposal">
                            <span
                                v-if="row.memo !== '—'"
                                class="disposal-tag"
                                >{{ row.memo }}</span
                            >
                            <span v-else>—</span>
                        </td>
                        <td
                            :style="{
                                color: row.isCurrentRow
                                    ? 'var(--teal)'
                                    : 'var(--slate-lt)',
                            }"
                            style="font-size: 10px"
                        >
                            {{ row.ak }}
                        </td>
                    </tr>
                    <tr v-if="!store.currentPatient.monitoringRecords?.length">
                        <td
                            colspan="14"
                            style="color: var(--slate-lt); padding: 20px"
                        >
                            該病患目前無今日透析生命徵象數據。
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { useDoctorStore } from "@/store/doctorStore";
const store = useDoctorStore();
</script>

<style scoped>
.grid-section {
    background: white;
    border-bottom: 1.5px solid var(--border);
    flex-shrink: 0;
}
.grid-wrap {
    overflow-x: auto;
}
.monitoring-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
.monitoring-table th {
    background: var(--pri-dk);
    color: white;
    padding: 5px 4px;
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
}
.monitoring-table td {
    padding: 5px 4px;
    text-align: center;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
    font-size: 12px;
    cursor: pointer;
    white-space: nowrap;
}
.monitoring-table td.time-cell {
    background: var(--pri-lt);
    color: var(--pri);
    font-weight: 700;
    text-align: left;
    padding-left: 10px;
    position: sticky;
    left: 0;
    z-index: 5;
    border-right: 2px solid var(--pri-bd);
}
.monitoring-table td.time-cell.add {
    background: var(--amber-lt);
    color: var(--amber);
}
.monitoring-table tr.current-row td {
    background: #eff6ff;
}
.monitoring-table td.bp {
    font-weight: 700;
}
.monitoring-table td.warn {
    color: var(--amber);
    font-weight: 700;
}
.monitoring-table td.empty {
    color: #cbd5e1;
}
.monitoring-table td.disposal {
    text-align: left;
    font-size: 11px;
    color: var(--slate);
}
.disposal-tag {
    background: var(--amber-lt);
    color: var(--amber);
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 10px;
    font-weight: 600;
}
</style>

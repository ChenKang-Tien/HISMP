<template>
    <div
        class="order-pool"
        id="order-pool"
        style="background: white; border-top: 2px solid #fde68a; flex-shrink: 0"
    >
        <div
            class="pool-hdr"
            style="
                font-size: 11px;
                font-weight: 700;
                color: #92400e;
                margin-bottom: 5px;
                display: flex;
                align-items: center;
                gap: 4px;
            "
        >
            <i class="ti ti-clipboard-list"></i>
            <span
                >🧾 今日臨時醫囑與用藥處置池（{{
                    activeOrdersCount
                }}筆1記錄）</span
            >
            <span
                style="
                    font-size: 10px;
                    color: var(--slate-lt);
                    margin-left: auto;
                    font-weight: 400;
                "
            >
                醫師開立 ・ 執行完成後膠囊自動消失
            </span>
        </div>

        <table
            class="pool-table"
            style="width: 100%; border-collapse: collapse; font-size: 11px"
        >
            <thead>
                <tr>
                    <th
                        style="
                            background: #fffbeb;
                            padding: 3px 6px;
                            text-align: left;
                            color: #92400e;
                            font-weight: 700;
                            border-bottom: 1.5px solid #fde68a;
                            font-size: 10px;
                        "
                    >
                        🕒 時間
                    </th>
                    <th
                        style="
                            background: #fffbeb;
                            padding: 3px 6px;
                            text-align: left;
                            color: #92400e;
                            font-weight: 700;
                            border-bottom: 1.5px solid #fde68a;
                            font-size: 10px;
                        "
                    >
                        👨‍⚕️ 醫師
                    </th>
                    <th
                        style="
                            background: #fffbeb;
                            padding: 3px 6px;
                            text-align: left;
                            color: #92400e;
                            font-weight: 700;
                            border-bottom: 1.5px solid #fde68a;
                            font-size: 10px;
                        "
                    >
                        💊 醫囑內容
                    </th>
                    <th
                        style="
                            background: #fffbeb;
                            padding: 3px 6px;
                            text-align: left;
                            color: #92400e;
                            font-weight: 700;
                            border-bottom: 1.5px solid #fde68a;
                            font-size: 10px;
                        "
                    >
                        護理補充
                    </th>
                    <th
                        style="
                            background: #fffbeb;
                            padding: 3px 6px;
                            text-align: left;
                            color: #92400e;
                            font-weight: 700;
                            border-bottom: 1.5px solid #fde68a;
                            font-size: 10px;
                        "
                    >
                        ⚡ 核對執行
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="order in currentPatientOrders" :key="order.id">
                    <td
                        style="
                            padding: 4px 6px;
                            border-bottom: 1px solid #f1f5f9;
                            vertical-align: top;
                            text-align: left;
                        "
                    >
                        {{ order.time }}
                    </td>
                    <td
                        style="
                            padding: 4px 6px;
                            border-bottom: 1px solid #f1f5f9;
                            vertical-align: top;
                            text-align: left;
                        "
                    >
                        {{ order.doctor }}
                    </td>
                    <td
                        style="
                            padding: 4px 6px;
                            border-bottom: 1px solid #f1f5f9;
                            vertical-align: top;
                            text-align: left;
                        "
                    >
                        {{ order.content }}
                        <span
                            v-if="order.isUrgent"
                            style="
                                font-size: 9px;
                                display: block;
                                color: #b45309;
                                font-weight: 700;
                                margin-top: 2px;
                            "
                        >
                            ⚠️ 緊急處置
                        </span>
                    </td>
                    <td
                        style="
                            padding: 4px 6px;
                            border-bottom: 1px solid #f1f5f9;
                            vertical-align: top;
                            text-align: left;
                        "
                    >
                        <span
                            v-if="order.isExecuted"
                            class="nurse-note-badge"
                            style="
                                font-size: 10px;
                                background: #f0fdfa;
                                border: 1px solid #99f6e4;
                                border-radius: 4px;
                                padding: 1px 6px;
                                color: #0f766e;
                                display: block;
                                width: fit-content;
                            "
                        >
                            {{ order.complement || "無補充" }}
                        </span>
                        <input
                            v-else
                            v-model="order.complement"
                            type="text"
                            style="
                                font-size: 10px;
                                border: 1px solid #e2e8f0;
                                border-radius: 4px;
                                padding: 2px 5px;
                                width: 100%;
                                outline: none;
                            "
                            placeholder="護理補充..."
                        />
                    </td>
                    <td
                        style="
                            padding: 4px 6px;
                            border-bottom: 1px solid #f1f5f9;
                            vertical-align: top;
                            text-align: left;
                        "
                    >
                        <span
                            v-if="order.isExecuted"
                            class="exec-done"
                            style="
                                font-size: 10px;
                                color: #16a34a;
                                font-weight: 700;
                            "
                        >
                            ✅ {{ order.execNurse }} {{ order.execTime }}
                        </span>
                        <button
                            v-else
                            class="exec-btn"
                            style="
                                font-size: 10px;
                                background: #f0fdf4;
                                color: #15803d;
                                border: 1.5px solid #86efac;
                                border-radius: 4px;
                                padding: 2px 7px;
                                cursor: pointer;
                                white-space: nowrap;
                                font-weight: 600;
                            "
                            @click="executeOrder(order)"
                        >
                            ☑ 確認執行
                        </button>
                    </td>
                </tr>

                <tr v-if="currentPatientOrders.length === 0">
                    <td
                        colspan="5"
                        style="
                            text-align: center;
                            color: #94a3b8;
                            padding: 12px;
                            font-size: 11px;
                        "
                    >
                        本班暫無待執行醫囑
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();

// 100% 復刻原稿 JavaScript 的多病患醫囑資料池狀態機
const allOrdersPool = ref({
    薛玉鳳: [
        {
            id: 1,
            time: "09:32",
            doctor: "張院醫師",
            content: "六零增速 0.5mL/min；擴充液1支",
            complement: "高遠1號已備妥",
            isUrgent: true,
            isExecuted: true,
            execNurse: "楚心瑜",
            execTime: "10:22",
        },
        {
            id: 2,
            time: "10:15",
            doctor: "張院醫師",
            content: "N/S 100ml 快速輸注，UFR調降",
            complement: "",
            isUrgent: false,
            isExecuted: false,
            execNurse: "",
            execTime: "",
        },
    ],
    "林*芳": [
        {
            id: 3,
            time: "09:32",
            doctor: "張院醫師",
            content: "Norvasc 5mg IV 一次",
            complement: "",
            isUrgent: true,
            isExecuted: false,
            execNurse: "",
            execTime: "",
        },
    ],
    "李*美": [], // 模擬當班無醫囑病患
});

// 根據目前選中的病患姓名 dynamic 撈取對應表格列
const currentPatientOrders = computed(() => {
    const name = store.currentPatient.name || "薛玉鳳";
    return allOrdersPool.value[name] || [];
});

// 統計待處理的計數徽章
const activeOrdersCount = computed(() => {
    return currentPatientOrders.value.filter((o) => !o.isExecuted).length;
});

// 點擊執行確認，變更狀態並寫入全院不滅病歷日誌 (留痕稽核相容)
const executeOrder = (order) => {
    const timeStr = new Date().toTimeString().slice(0, 5);
    order.isExecuted = true;
    order.execNurse = "楚心瑜";
    order.execTime = timeStr;

    // 連動寫入左側護理記錄日誌
    store.addNursingRecord(
        `[醫囑執行] 完成核對並執行醫師臨時醫囑：${order.content}。護理補充：${order.complement || "無"}`,
    );
};
</script>

<style scoped>
/* 確保表格內的輸入框高亮色系正常 */
input:focus {
    border-color: #14b8a6 !important;
}
</style>

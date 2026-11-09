<template>
    <div
        class="order-pool"
        id="order-pool"
        style="
            background: #ffffff;
            border-top: 2.5px solid #fde68a;
            flex-shrink: 0;
            padding: 9px 12px;
        "
    >
        <div
            class="pool-hdr"
            @click="toggleCollapse"
            style="
                font-size: 13.5px;
                font-weight: 700;
                color: #92400e;
                margin-bottom: 0px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                cursor: pointer;
                user-select: none;
                position: relative;
            "
        >
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="ti ti-clipboard-list" style="font-size: 17px;"></i>
                <span>🧾 今日臨時醫囑與處置池</span>
                <span
                    :style="{
                        background: activeOrdersCount > 0 ? '#fef3c7' : '#f0fdf4',
                        color: activeOrdersCount > 0 ? '#b45309' : '#15803d',
                        border:
                            '1px solid ' +
                            (activeOrdersCount > 0 ? '#fcd34d' : '#86efac'),
                        borderRadius: '12px',
                        padding: '1px 8px',
                        fontSize: '11.5px',
                        fontWeight: '700',
                    }"
                >
                    {{ activeOrdersCount }} 筆待處理 / 共
                    {{ (store.ordersPool || []).length }} 筆
                </span>
            </div>

            <!-- 拉把位置移到正中間 -->
            <div
                class="collapse-rail"
                @click.stop="toggleCollapse"
                :title="isCollapsed ? '展開處置池' : '收折處置池'"
                style="position: absolute; left: 50%; transform: translateX(-50%);"
            >
                <div
                    class="collapse-rail-inner"
                    :class="{ active: isCollapsed }"
                >
                    {{ isCollapsed ? "▲" : "▼" }}
                </div>
            </div>

            <div
                class="desc-text"
                style="
                    font-size: 11.5px;
                    color: #94a3b8;
                    font-weight: 500;
                "
            >
                醫師開立 ・ 執行/延後/核對自動存證留痕
            </div>
        </div>

        <div v-show="!isCollapsed" style="overflow-x: auto; margin-top: 7px;">
            <table
                class="pool-table"
                style="width: 100%; border-collapse: collapse; font-size: 12.5px;"
            >
                <thead>
                    <tr>
                        <th style="background: #fffbeb; padding: 6px 8px; text-align: left; color: #92400e; font-weight: 700; border-bottom: 1.5px solid #fde68a; font-size: 12px; width: 65px;">
                            🕒 時間
                        </th>
                        <th style="background: #fffbeb; padding: 6px 8px; text-align: left; color: #92400e; font-weight: 700; border-bottom: 1.5px solid #fde68a; font-size: 12px; width: 85px;">
                            👨‍⚕️ 醫師
                        </th>
                        <th style="background: #fffbeb; padding: 6px 8px; text-align: left; color: #92400e; font-weight: 700; border-bottom: 1.5px solid #fde68a; font-size: 12px; width: 100px;">
                            🏷️ 類別
                        </th>
                        <th style="background: #fffbeb; padding: 6px 8px; text-align: left; color: #92400e; font-weight: 700; border-bottom: 1.5px solid #fde68a; font-size: 12px;">
                            💊 醫囑與處置內容
                        </th>
                        <th style="background: #fffbeb; padding: 6px 8px; text-align: left; color: #92400e; font-weight: 700; border-bottom: 1.5px solid #fde68a; font-size: 12px; width: 170px;">
                            📝 護理補充 / 備註
                        </th>
                        <th style="background: #fffbeb; padding: 6px 8px; text-align: center; color: #92400e; font-weight: 700; border-bottom: 1.5px solid #fde68a; font-size: 12px; width: 155px;">
                            ⚡ 核對處理
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="order in store.ordersPool"
                        :key="order.table + '_' + order.id"
                        :style="{
                            background: order.isExecuted ? '#fafafa' : (order.isPostponed ? '#fffbeb' : (order.isUrgent ? '#fffdf5' : '#ffffff')),
                        }"
                    >
                        <td style="padding: 7px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-weight: 600; color: #475569;">
                            {{ order.time }}
                        </td>
                        <td style="padding: 7px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; font-weight: 600;">
                            {{ order.doctor }}
                        </td>
                        <td style="padding: 7px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;">
                            <span
                                style="
                                    font-size: 11px;
                                    padding: 2px 6px;
                                    border-radius: 4px;
                                    font-weight: 600;
                                "
                                :style="getCategoryBadgeStyle(order.category)"
                            >
                                {{ order.category }}
                            </span>
                        </td>
                        <td style="padding: 7px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #0f172a; line-height: 1.4;">
                            <span :style="{ fontWeight: order.isUrgent ? '700' : '500' }">{{ order.content }}</span>
                            <span
                                v-if="order.isUrgent"
                                style="
                                    font-size: 11px;
                                    display: inline-block;
                                    color: #b45309;
                                    background: #fef3c7;
                                    border: 1px solid #fcd34d;
                                    border-radius: 4px;
                                    padding: 1px 6px;
                                    font-weight: 700;
                                    margin-left: 6px;
                                "
                            >
                                ⚠️ 緊急處置
                            </span>
                        </td>
                        <td style="padding: 7px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;">
                            <span
                                v-if="order.isExecuted"
                                class="nurse-note-badge"
                                style="
                                    font-size: 11.5px;
                                    background: #f0fdfa;
                                    border: 1px solid #99f6e4;
                                    border-radius: 4px;
                                    padding: 2px 7px;
                                    color: #0f766e;
                                    display: inline-block;
                                    font-weight: 500;
                                "
                            >
                                {{ order.complement || "已完成核對" }}
                            </span>
                            <span
                                v-else-if="order.isPostponed"
                                style="
                                    font-size: 11.5px;
                                    background: #fef3c7;
                                    border: 1px solid #fde68a;
                                    border-radius: 4px;
                                    padding: 2px 7px;
                                    color: #b45309;
                                    display: inline-block;
                                    font-weight: 600;
                                "
                            >
                                ⏸ 延後：{{ order.reason || "稍後追蹤" }}
                            </span>
                            <input
                                v-else
                                v-model="order.complement"
                                type="text"
                                style="
                                    font-size: 12px;
                                    border: 1px solid #cbd5e1;
                                    border-radius: 5px;
                                    padding: 4px 7px;
                                    width: 100%;
                                    box-sizing: border-box;
                                    outline: none;
                                    transition: border-color 0.15s ease;
                                "
                                :placeholder="order.action_type === 'confirm' ? '核對備註...' : '填寫護理補充...'"
                            />
                        </td>
                        <td style="padding: 7px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; text-align: center;">
                            <!-- 已執行 -->
                            <span
                                v-if="order.isExecuted"
                                style="
                                    font-size: 12px;
                                    color: #15803d;
                                    font-weight: 700;
                                    background: #f0fdf4;
                                    border: 1px solid #bbf7d0;
                                    border-radius: 5px;
                                    padding: 3px 8px;
                                    display: inline-block;
                                "
                            >
                                ✅ {{ order.execNurse || "護理師" }}
                            </span>

                            <!-- 處方確認類按鈕 -->
                            <div v-else-if="order.action_type === 'confirm'" style="display: flex; gap: 4px; justify-content: center;">
                                <button
                                    class="exec-btn"
                                    style="
                                        font-size: 12px;
                                        background: #2563eb;
                                        color: #ffffff;
                                        border: 1px solid #1d4ed8;
                                        border-radius: 5px;
                                        padding: 4px 10px;
                                        cursor: pointer;
                                        white-space: nowrap;
                                        font-weight: 700;
                                    "
                                    @click="handleAction(order, 'confirm')"
                                >
                                    ☑ 核對確認
                                </button>
                            </div>

                            <!-- 臨時處置類按鈕（執行 或 延後） -->
                            <div v-else style="display: flex; gap: 4px; justify-content: center;">
                                <button
                                    class="exec-btn"
                                    style="
                                        font-size: 12px;
                                        background: #0f766e;
                                        color: #ffffff;
                                        border: 1px solid #0d9488;
                                        border-radius: 5px;
                                        padding: 4px 8px;
                                        cursor: pointer;
                                        white-space: nowrap;
                                        font-weight: 700;
                                    "
                                    @click="handleAction(order, 'execute')"
                                >
                                    ☑ 執行
                                </button>
                                <button
                                    class="postpone-btn"
                                    style="
                                        font-size: 12px;
                                        background: #f8fafc;
                                        color: #64748b;
                                        border: 1px solid #cbd5e1;
                                        border-radius: 5px;
                                        padding: 4px 7px;
                                        cursor: pointer;
                                        white-space: nowrap;
                                        font-weight: 600;
                                    "
                                    @click="handleAction(order, 'postpone')"
                                    title="暫緩並標記延後"
                                >
                                    ⏸ 延後
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!store.ordersPool || store.ordersPool.length === 0">
                        <td
                            colspan="6"
                            style="
                                text-align: center;
                                color: #94a3b8;
                                padding: 16px;
                                font-size: 13px;
                            "
                        >
                            <i class="ti ti-check" style="color: #10b981; margin-right: 4px;"></i>
                            當前透析場次暫無待執行之臨時醫囑與處置
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();
const isCollapsed = ref(false);

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
};

// 統計未執行的待處理醫囑數量
const activeOrdersCount = computed(() => {
    return (store.ordersPool || []).filter((o) => !o.isExecuted && !o.isPostponed).length;
});

const getCategoryBadgeStyle = (category) => {
    switch (category) {
        case "理學":
            return { background: "#f5f3ff", color: "#6d28d9", border: "1px solid #ddd6fe" };
        case "實驗室":
            return { background: "#fefce8", color: "#854d0e", border: "1px solid #fef08a" };
        case "醫器":
            return { background: "#ecfeff", color: "#0e7490", border: "1px solid #a5f3fc" };
        case "一般給藥":
            return { background: "#eff6ff", color: "#1d4ed8", border: "1px solid #bfdbfe" };
        case "其他":
            return { background: "#f8fafc", color: "#475569", border: "1px solid #cbd5e1" };
        case "透析參數長期":
            return { background: "#eef2ff", color: "#4338ca", border: "1px solid #c7d2fe" };
        case "透析參數短期":
            return { background: "#fdf4ff", color: "#86198f", border: "1px solid #f5d0fe" };
        case "乾體重":
            return { background: "#f0fdfa", color: "#0f766e", border: "1px solid #99f6e4" };
        case "EPO":
            return { background: "#fff1f2", color: "#be123c", border: "1px solid #fecdd3" };
        case "鐵劑":
            return { background: "#fff7ed", color: "#c2410c", border: "1px solid #fed7aa" };
        case "透析用藥":
            return { background: "#ecfdf5", color: "#047857", border: "1px solid #a7f3d0" };
        default:
            return { background: "#f1f5f9", color: "#334155", border: "1px solid #e2e8f0" };
    }
};

// 處理醫囑處置動作：execute (執行) / postpone (延後) / confirm (確認)
const handleAction = async (order, action) => {
    if (!order) return;
    let payload = { complement: order.complement || "" };

    if (action === "postpone") {
        const reason = prompt("請輸入延後執行之臨床原因（如：病患目前低血壓暫緩、待檢查報告回傳）：", "待後續追蹤");
        if (reason === null) return; // 使用者按取消
        payload.reason = reason || "待後續追蹤";
    }

    const success = await store.executeOrder(order, action, payload);
    if (!success) {
        alert("操作失敗，請重試！");
    }
};
</script>

<style scoped>
/* 收合手把 (與處置池琥珀色系一致) */
.collapse-rail {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
    background: linear-gradient(
        to bottom,
        transparent,
        rgba(253, 230, 138, 0.4)
    );
    transition: background 0.15s ease;
}

.collapse-rail:hover {
    background: linear-gradient(to bottom, transparent, #fffbeb);
}

.collapse-rail-inner {
    width: 38px;
    height: 18px;
    background: #fde68a;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #92400e;
    font-weight: 700;
    transition: all 0.15s ease;
}

.collapse-rail-inner.active {
    background: #fde68a;
    color: #92400e;
}

.collapse-rail:hover .collapse-rail-inner {
    background: #fcd34d;
}

.pool-table tbody tr:hover {
    background-color: #f8fafc !important;
}
input:focus {
    border-color: #d97706 !important;
    box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.15);
}
.exec-btn:hover {
    opacity: 0.9;
}
.postpone-btn:hover {
    background: #e2e8f0 !important;
    color: #334155 !important;
}
</style>

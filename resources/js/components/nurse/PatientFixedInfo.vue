<template>
    <!-- 收合軌道 (Rail) 完全對齊原稿設計 -->
    <div
        class="collapse-rail"
        @click="store.isFixedColCollapsed = !store.isFixedColCollapsed"
        title="收折"
    >
        <div class="collapse-rail-inner">
            {{ store.isFixedColCollapsed ? "▶" : "◀" }}
        </div>
    </div>

    <!-- 主內嵌軀殼 -->
    <div v-show="!store.isFixedColCollapsed" class="fixed-body">
        <!-- ① 當週 HCT / HGB（原稿三格橫排 LW/TW/AT） -->
        <div class="fh"><i class="ti ti-droplet-half-2"></i>當週 HCT / HGB</div>
        <div class="hct-row-layout">
            <div class="hct-box-cell bg-teal-lt border-teal-bd">
                <div class="hct-lbl-mini text-teal font-700">LW</div>
                <div class="hct-val text-teal">{{ store.hctLW }}%</div>
                <div class="hct-lbl-mini text-slate-lt">上週</div>
            </div>
            <div class="hct-box-cell bg-slate-ul border-default">
                <div class="hct-lbl-mini text-slate font-700">TW</div>
                <div class="hct-val" :class="hctTwColorClass">
                    {{ store.hctTW ? store.hctTW + "%" : "—" }}
                </div>
                <div class="hct-lbl-mini text-slate-lt">本週</div>
            </div>
            <!-- AT 格在原稿中預設為隱藏或條件顯示，這裡依據 store 判斷，樣式完美對齊 -->
            <div
                v-if="isAtUnlocked"
                class="hct-box-cell bg-slate-ul border-default"
            >
                <div class="hct-lbl-mini text-slate font-700">AT</div>
                <div class="hct-val text-slate">
                    {{ store.hctAT ? store.hctAT + "%" : "—" }}
                </div>
                <div class="hct-lbl-mini text-slate-lt">重測</div>
            </div>
        </div>

        <!-- ② 透前報到過磅 + 扣重池 -->
        <div class="fh fh-flex">
            <span><i class="ti ti-scale"></i>透析體重過磅</span>
            <span
                class="edit-icon-btn"
                @click="triggerWeightPrompt"
                title="填寫體重"
                >✏️</span
            >
        </div>
        <div class="weight-grid-layout">
            <div class="weight-main-box bg-teal-lt border-teal-bd">
                <div class="w-mini-hdr text-teal font-700">透前</div>
                <div class="w-sub-split">
                    <div>
                        <div class="w-val-text text-teal">
                            {{
                                store.preRawWeight
                                    ? store.preRawWeight + " kg"
                                    : "—"
                            }}
                        </div>
                        <div class="w-lbl-sub text-slate-lt">未扣</div>
                    </div>
                    <div class="v-line border-teal-bd"></div>
                    <div>
                        <div class="w-val-text text-teal-dk font-700">
                            {{
                                store.preAdjWeight
                                    ? store.preAdjWeight + " kg"
                                    : "—"
                            }}
                        </div>
                        <div class="w-lbl-sub text-slate-lt">已扣</div>
                    </div>
                </div>
            </div>
            <div class="weight-main-box bg-slate-ul border-default">
                <div class="w-mini-hdr text-slate font-700">透後</div>
                <div class="w-sub-split">
                    <div>
                        <div class="w-val-text text-slate">
                            {{
                                store.postRawWeight
                                    ? store.postRawWeight + " kg"
                                    : "—"
                            }}
                        </div>
                        <div class="w-lbl-sub text-slate-lt">未扣</div>
                    </div>
                    <div class="v-line border-default"></div>
                    <div>
                        <div class="w-val-text text-slate font-700">
                            {{
                                store.postRawWeight
                                    ? store.postRawWeight + " kg"
                                    : "—"
                            }}
                        </div>
                        <div class="w-lbl-sub text-slate-lt">已扣</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 扣重池明細 -->
        <div class="fh fh-flex">
            <div class="w-detail-title">扣重池明細</div>
            <span
                class="edit-icon-btn"
                @click="triggerDeductionManage"
                title="管理扣重"
                >✏️</span
            >
        </div>
        <div class="fc-deduction-list">
            <div
                v-for="d in store.deductions"
                :key="d.id"
                class="deduct-item-row"
            >
                <span>{{ d.name }}</span>
                <span class="text-teal">-{{ d.weight.toFixed(1) }} kg</span>
            </div>
            <div class="deduct-total-row">
                <span class="text-slate-lt">合計</span>
                <span class="text-teal font-700"
                    >-{{ store.deductionTotal.toFixed(1) }} kg</span
                >
            </div>
        </div>

        <!-- 乾體重（醫囑帶入，唯讀） -->
        <div class="status-bar-row bg-slate-ul border-default">
            <div>
                <div class="sb-lbl text-slate-lt">乾體重（醫囑帶入，唯讀）</div>
                <div class="sb-val text-dark">
                    {{ store.dryWeight }}
                    <span class="sb-unit text-slate-lt">kg</span>
                </div>
            </div>
            <i class="ti ti-lock text-slate-lt"></i>
        </div>

        <!-- 今日應調水（自動計算） -->
        <div class="status-bar-row bg-amber-lt border-amber-bd">
            <div>
                <div class="sb-lbl text-amber">今日應調水（自動計算）</div>
                <div class="sb-val text-amber font-700">
                    {{ store.targetUF > 0 ? store.targetUF.toFixed(1) : "0.0" }}
                    <span class="sb-unit">kg</span>
                </div>
            </div>
            <i class="ti ti-calculator text-amber"></i>
        </div>

        <!-- 今日實際調水（透析機帶入） -->
        <div class="status-bar-row bg-teal-lt border-teal-bd">
            <div class="flex-1">
                <div class="sb-lbl text-teal">今日實際調水（透析機帶入）</div>
                <div class="sb-val text-teal font-700">
                    <span v-if="store.actualUfWeight" class="text-teal"
                        >{{ store.actualUfWeight }} kg</span
                    >
                    <span v-else class="text-teal-light-dash">— kg</span>
                </div>
            </div>
            <span
                class="edit-icon-btn text-teal"
                @click="triggerActualUfPrompt"
                title="點入調整"
                >✏️</span
            >
        </div>

        <!-- 醫師更新乾體重模擬按鈕 -->
        <button class="mock-notify-btn" @click="mockDoctorUpdateDryWeight">
            🔔 模擬：醫師更新乾體重通知
        </button>

        <!-- ④ 透析機（DL-093） -->
        <div class="fh"><i class="ti ti-device-heart-monitor"></i>透析機</div>
        <div class="machine-grid-layout">
            <div class="machine-box-cell bg-slate-ul border-default">
                <div class="m-lbl-mini text-slate-lt">機型</div>
                <div class="m-val-text font-700">AK-98</div>
            </div>
            <div class="machine-box-cell bg-slate-ul border-default">
                <div class="m-lbl-mini text-slate-lt">編碼</div>
                <div class="m-val-text font-700">M-01</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();

// 判定 HCT 追加觀測是否開啟
const isAtUnlocked = computed(() => {
    if (!store.hctTW) return false;
    return Math.abs(store.hctTW - store.hctLW) > 2;
});

// TW 數值顏色連動 Class
const hctTwColorClass = computed(() => {
    if (!store.hctTW) return "text-slate-lt";
    return isAtUnlocked.value ? "text-amber font-700" : "text-teal font-700";
});

// 彈出式視窗行為對齊 (完全還原原稿的原生行為)
const triggerWeightPrompt = () => {
    const w = prompt(
        "請輸入透前原始體重（如 799 代表 79.9）：",
        store.preRawWeight || "",
    );
    if (w) store.preRawWeight = parseFloat(w);
};

const triggerActualUfPrompt = () => {
    const u = prompt("請調整實際調水量（kg）：", store.actualUfWeight || "3.5");
    if (u) store.actualUfWeight = parseFloat(u);
};

const triggerDeductionManage = () => {
    const item = prompt("請輸入新增扣重項目名稱（如：便當）：", "自訂衣物");
    const wt = prompt("請輸入扣重重量（kg）：", "0.5");
    if (item && wt) {
        store.deductions.push({
            id: Date.now(),
            name: item,
            weight: parseFloat(wt),
        });
    }
};

const mockDoctorUpdateDryWeight = () => {
    if (
        confirm(
            "⚠️ 模擬醫師更新乾體重通知：\n張院醫師已將乾體重更新為 61.0 kg（原 59.5 kg）。\n系統將連動重新計算應調水，是否確認接收？",
        )
    ) {
        store.dryWeight = 61.0;
    }
};
</script>

<style scoped>
/* ════ 100% 複製原稿his_nursing_ui_v24.html 固定欄內部所有樣式 ════ */
.fixed-body {
    flex: 1;
    overflow-y: auto;
    padding: 9px 20px 9px 9px; /* 完全對齊原稿 */
    box-sizing: border-box;
}

/* 側邊拉折線樣式 */
.collapse-rail {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 5;
    background: linear-gradient(
        to right,
        transparent,
        rgba(153, 246, 228, 0.4)
    );
}
.collapse-rail:hover {
    background: linear-gradient(to right, transparent, #f0fdfa);
}
.collapse-rail-inner {
    width: 13px;
    background: #99f6e4;
    border-radius: 3px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    color: #0f766e;
    font-weight: 700;
}

/* 標題與微小元件 */
.fh {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 3px;
    margin-top: 4px;
}
.fh-flex {
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
}
.w-detail-title {
    font-size: 9px;
    color: #475569;
    font-weight: 700;
}
.edit-icon-btn {
    cursor: pointer;
    color: #0f766e;
    font-size: 12px;
    user-select: none;
}
.flex-1 {
    flex: 1;
}

/* HCT 區塊格 */
.hct-row-layout {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3px;
    margin-bottom: 4px;
}
.hct-box-cell {
    border-radius: 6px;
    padding: 4px;
    text-align: center;
    border: 1.5px solid transparent;
}
.hct-lbl-mini {
    font-size: 8px;
}
.hct-val {
    font-size: 12px;
    font-weight: 700;
}

/* 體重核心大方塊 */
.weight-grid-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    margin-bottom: 5px;
}
.weight-main-box {
    border-radius: 7px;
    padding: 5px;
    text-align: center;
    border: 1.5px solid transparent;
}
.w-mini-hdr {
    font-size: 8px;
    margin-bottom: 3px;
}
.w-sub-split {
    display: flex;
    justify-content: space-around;
    align-items: center;
}
.w-val-text {
    font-size: 12px;
}
.w-lbl-sub {
    font-size: 8px;
}
.v-line {
    border-left: 1px solid;
    height: 16px;
    opacity: 0.7;
}

/* 扣重明細小方框 */
.fc-deduction-list {
    background: #f1f5f9;
    border-radius: 6px;
    padding: 4px 7px;
    margin-bottom: 5px;
    font-size: 10px;
}
.deduct-item-row {
    display: flex;
    justify-content: space-between;
    color: #374151;
    padding: 1px 0;
}
.deduct-total-row {
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #e2e8f0;
    margin-top: 2px;
    padding-top: 2px;
    font-weight: 700;
}

/* 帶有狀態與底色之資料橫條列 */
.status-bar-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 6px;
    padding: 4px 8px;
    margin-bottom: 4px;
    border: 1.5px solid transparent;
}
.sb-lbl {
    font-size: 8px;
}
.sb-val {
    font-size: 14px;
}
.sb-unit {
    font-size: 9px;
}

/* 模擬更新按鈕 */
.mock-notify-btn {
    width: 100%;
    font-size: 9px;
    color: #b45309;
    background: #fffbeb;
    border: 1px dashed #fde68a;
    border-radius: 5px;
    padding: 3px 6px;
    cursor: pointer;
    margin-bottom: 6px;
    text-align: center;
}

/* 透析機唯讀格 */
.machine-grid-layout {
    display: flex;
    gap: 4px;
    margin-bottom: 6px;
}
.machine-box-cell {
    flex: 1;
    border-radius: 6px;
    padding: 5px;
    text-align: center;
    border: 1.5px solid transparent;
}
.m-lbl-mini {
    font-size: 9px;
}
.m-val-text {
    font-size: 11px;
    color: #1e293b;
}

/* 顏色變數對齊原稿原色 */
.bg-teal-lt {
    background-color: #f0fdfa;
} /* --teal-lt */
.border-teal-bd {
    border-color: #99f6e4;
} /* --teal-bd */
.bg-slate-ul {
    background-color: #f1f5f9;
} /* --slate-ul */
.bg-amber-lt {
    background-color: #fffbeb;
} /* --amber-lt */
.border-amber-bd {
    border-color: #fde68a;
} /* --amber-bd */
.border-default {
    border-color: #e2e8f0;
} /* --border */

.text-teal {
    color: #0f766e;
}
.text-teal-dk {
    color: #134e4a;
}
.text-teal-light-dash {
    color: #cbd5e1;
}
.text-slate {
    color: #475569;
}
.text-slate-lt {
    color: #94a3b8;
}
.text-amber {
    color: #b45309;
}
.text-dark {
    color: #1e293b;
}
.font-700 {
    font-weight: 700;
}
</style>

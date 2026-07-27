<template>
    <!-- 👩‍⚕️ 還原 HISMP V24/V27 頂級護理綠標頭條 (#f0fdfa) -->
    <div class="nursing-bar-outer" style="border-top: 2px solid #99f6e4">
        <!-- 核心按鈕與狀態控制列 -->
        <div
            class="nursing-bar-hdr"
            style="
                padding: 6px 12px;
                background-color: #f0fdfa;
                border-bottom: 1px solid #e2e8f0;
                font-size: 11px;
                font-weight: 700;
                color: #0f766e;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 6px;
            "
        >
            <span style="display: flex; align-items: center; gap: 4px">
                <i class="ti ti-notes"></i> 護理記錄
            </span>

            <!-- 🌟 三大核心按鈕觸發門鏈 -->
            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    flex-wrap: wrap;
                "
            >
                <button
                    @click="emit('open-modal', 'equipment')"
                    style="
                        font-size: 10px;
                        color: #0f766e;
                        background: #e0f2fe;
                        border: 1.5px solid #bae6fd;
                        border-radius: 5px;
                        padding: 3px 10px;
                        font-weight: 700;
                        cursor: pointer;
                        transition: opacity 0.15s;
                    "
                    onmouseover="this.style.opacity = 0.8"
                    onmouseout="this.style.opacity = 1"
                >
                    🛡️ 新增保護設備
                </button>

                <button
                    @click="emit('open-modal', 'nw')"
                    style="
                        font-size: 10px;
                        color: #7c3aed;
                        background: #f3e8ff;
                        border: 1.5px solid #e9d5ff;
                        border-radius: 5px;
                        padding: 3px 10px;
                        font-weight: 700;
                        cursor: pointer;
                        transition: opacity 0.15s;
                    "
                    onmouseover="this.style.opacity = 0.8"
                    onmouseout="this.style.opacity = 1"
                >
                    ➕ 新增 📷 Nurse Watching
                </button>

                <button
                    @click="emit('open-modal', 'record')"
                    style="
                        font-size: 10px;
                        color: #0f766e;
                        background: #ccfbf1;
                        border: 1.5px solid #99f6e4;
                        border-radius: 5px;
                        padding: 3px 10px;
                        font-weight: 700;
                        cursor: pointer;
                        transition: opacity 0.15s;
                    "
                    onmouseover="this.style.opacity = 0.8"
                    onmouseout="this.style.opacity = 1"
                >
                    ➕ 新增護理記錄
                </button>

                <span
                    style="
                        font-size: 9px;
                        color: #94a3b8;
                        font-weight: normal;
                        margin-left: 4px;
                    "
                >
                    ⏱ 主動暫存：{{ store.autoSaveTime }}
                </span>
            </div>
        </div>

        <!-- 實時護理記錄顯示槽（與大腦 Pinia 時間軸綁定） -->
        <div
            class="nursing-records-log-pool"
            style="
                overflow-y: auto;
                max-height: 110px;
                background: white;
                padding: 8px 12px;
                font-size: 11px;
                min-height: 50px;
            "
        >
            <div
                v-for="rec in store.nursingRecords"
                :key="rec.id"
                style="
                    padding: 4px 0;
                    border-bottom: 1px dashed #f1f5f9;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                "
            >
                <span
                    :style="{
                        textDecoration: rec.isDeleted ? 'line-through' : 'none',
                        color: rec.isDeleted ? '#94a3b8' : '#334155',
                    }"
                >
                    <b style="color: #0f766e; margin-right: 6px"
                        >[{{ rec.time }}]</b
                    >
                    {{ rec.content }}
                    <span
                        v-if="rec.isDeleted"
                        style="
                            color: #ef4444;
                            margin-left: 6px;
                            font-style: italic;
                            font-size: 10px;
                        "
                        >{{ rec.deletedMeta }}</span
                    >
                </span>
                <button
                    v-if="!rec.isDeleted"
                    @click="store.deleteNursingRecord(rec.id)"
                    style="
                        color: #ef4444;
                        border: none;
                        background: transparent;
                        cursor: pointer;
                        font-size: 10px;
                        font-weight: 600;
                    "
                >
                    註銷
                </button>
            </div>
            <div
                v-if="store.nursingRecords.length === 0"
                style="
                    color: #94a3b8;
                    text-align: center;
                    padding: 10px 0;
                    font-style: italic;
                "
            >
                今日當班尚無護理處置追蹤紀錄。
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();
const emit = defineEmits(["open-modal"]); // 拋出點擊事件通知主畫面

// 篩選出需要顯示的記錄 (包含未刪除的，以及被刪除但需要留在畫面上劃線留痕的)
const activeRecords = computed(() => store.nursingRecords);

// 動態判定記錄項目的基礎樣式
const getItemStyle = (isDeleted) => {
    return {
        display: "flex",
        alignItems: "flex-start",
        gap: "6px",
        padding: "5px 0",
        borderBottom: "1px solid var(--slate-ul)",
        opacity: isDeleted ? 0.6 : 1,
    };
};

// 動態判定加線留痕劃掉樣式 (EHR Audit)
const getContentStyle = (isDeleted) => {
    if (isDeleted) {
        return {
            textDecoration: "line-through",
            textDecorationColor: "var(--red)",
            textDecorationThickness: "1.5px",
            color: "var(--slate-lt)",
        };
    }
    return {};
};

// ════ 按鈕互動方法與原稿 JavaScript 對齊 ════
const openProtectiveEquipment = () => {
    alert("🛡️ 叫出 [新增保護設備] 臨床核對視窗");
};

const openNWCreate = () => {
    alert("📷 叫出 [新增 Nurse Watching] 傷口與穿刺點影像病歷輸入盤");
};

const openNursingRecordPopup = () => {
    const text = prompt("請輸入新增加的當班護理記錄：");
    if (text && text.trim()) {
        store.addNursingRecord(text.trim());
    }
};

const editNursingRecordById = (id, currentContent) => {
    const text = prompt("請修改護理記錄內容：", currentContent);
    if (text && text.trim() && text !== currentContent) {
        store.editNursingRecord(id, text.trim());
    }
};

const deleteNursingRecordById = (id) => {
    if (
        confirm(
            "🚨 醫學病歷安全提示：\n已送出之病歷不可實體抹除。系統將採用「加線劃掉並具名留痕」處理，確認執行？",
        )
    ) {
        store.deleteNursingRecord(id);
    }
};
</script>

<style scoped>
/* 補強刪除留痕小徽章樣式 */
.audit-trail-tag {
    background: var(--red-lt);
    color: var(--red);
    font-size: 9px;
    font-weight: 700;
    padding: 1px 4px;
    border-radius: 3px;
    margin-left: 4px;
    text-decoration: none !important;
    display: inline-block;
}
</style>

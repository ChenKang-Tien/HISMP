<template>
    <div
        id="nursing-record-bar"
        style="
            background: white;
            border-top: 2px solid #99f6e4;
            flex-shrink: 0;
            max-height: 280px;
            overflow: hidden;
        "
    >
        <div
            style="
                padding: 5px 10px;
                background-color: #f0fdfa;
                border-bottom: 1px solid #99f6e4;
                font-size: 11px;
                font-weight: 700;
                color: #0f766e;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 4px;
            "
        >
            <span
                ><i class="ti ti-notes" style="margin-right: 4px"></i
                >護理記錄</span
            >

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 5px;
                    flex-wrap: wrap;
                "
            >
                <button
                    @click="openProtectiveEquipment"
                    style="
                        font-size: 10px;
                        color: var(--green);
                        background: var(--green-lt);
                        border: 1.5px solid var(--green-bd);
                        border-radius: 5px;
                        padding: 2px 8px;
                        cursor: pointer;
                        white-space: nowrap;
                    "
                >
                    🛡️ 新增保護設備
                </button>
                <button
                    @click="openNWCreate"
                    style="
                        font-size: 10px;
                        color: var(--purple);
                        background: var(--purple-lt);
                        border: 1.5px solid var(--purple-bd);
                        border-radius: 5px;
                        padding: 2px 8px;
                        cursor: pointer;
                        white-space: nowrap;
                    "
                >
                    ➕ 新增 📷 Nurse Watching
                </button>
                <button
                    @click="openNursingRecordPopup"
                    style="
                        font-size: 10px;
                        color: var(--teal);
                        background: var(--teal-lt);
                        border: 1.5px solid var(--teal-bd);
                        border-radius: 5px;
                        padding: 2px 8px;
                        cursor: pointer;
                        white-space: nowrap;
                    "
                >
                    ➕ 新增護理記錄
                </button>
                <span
                    style="font-size: 9px; color: var(--slate-lt)"
                    id="nr-autosave"
                >
                    ⏱ 主動暫存：{{ store.autoSaveTime }}
                </span>
            </div>
        </div>

        <div
            style="overflow-y: auto; max-height: 200px; padding: 6px 10px"
            id="nursing-record-list"
        >
            <div
                v-for="rec in activeRecords"
                :key="rec.id"
                class="nr-item"
                :data-id="rec.id"
                :style="getItemStyle(rec.isDeleted)"
            >
                <span
                    style="flex: 1; font-size: 11px; color: #1e293b"
                    :style="getContentStyle(rec.isDeleted)"
                >
                    @{{ rec.time }} {{ rec.content }} {{ rec.nurse }}
                    <span v-if="rec.isDeleted" class="audit-trail-tag">{{
                        rec.deletedMeta
                    }}</span>
                </span>

                <button
                    v-if="!rec.isDeleted"
                    @click="editNursingRecordById(rec.id, rec.content)"
                    style="
                        font-size: 10px;
                        color: var(--teal);
                        background: none;
                        border: none;
                        cursor: pointer;
                        flex-shrink: 0;
                    "
                >
                    ✏️
                </button>
                <button
                    v-if="!rec.isDeleted"
                    @click="deleteNursingRecordById(rec.id)"
                    style="
                        font-size: 10px;
                        color: var(--red);
                        background: none;
                        border: none;
                        cursor: pointer;
                        flex-shrink: 0;
                    "
                >
                    🗑️
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();

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

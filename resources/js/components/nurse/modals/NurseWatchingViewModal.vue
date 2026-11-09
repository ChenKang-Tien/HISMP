<template>
    <div :class="['modal-overlay', { open: modelValue }]" @click.self="close">
        <div class="modal" style="max-width: 540px">
            <div class="modal-hdr">
                <span class="hdr-title">
                    <i class="ti ti-camera"></i> 📷 Nurse Watching 記錄
                </span>
                <button class="modal-x" @click="close">✕</button>
            </div>

            <!-- 無記錄狀態 -->
            <div v-if="records.length === 0" class="empty-state">
                <div class="empty-icon">📷</div>
                <div class="empty-text">本病患尚無 Nurse Watching 記錄</div>
                <button class="mbtn pri" style="margin-top: 14px;" @click="handleOpenCreate">
                    ➕ 立即新增 Nurse Watching
                </button>
            </div>

            <!-- 有記錄時顯示 -->
            <div v-else>
                <!-- 日期 / 記錄切換列 (DL-082) -->
                <div class="date-nav-bar">
                    <button
                        class="nav-arrow-btn"
                        :disabled="currentIndex <= 0"
                        @click="currentIndex--"
                        title="較新記錄"
                    >
                        ◀
                    </button>
                    <div class="nav-center">
                        <span class="nav-date-label">{{ currentDateLabel }}</span>
                        <span class="nav-counter">({{ currentIndex + 1 }} / {{ records.length }})</span>
                    </div>
                    <button
                        class="nav-arrow-btn"
                        :disabled="currentIndex >= records.length - 1"
                        @click="currentIndex++"
                        title="較早記錄"
                    >
                        ▶
                    </button>
                </div>

                <!-- 傷口照片區域 -->
                <div v-if="currentRecord?.images && currentRecord.images.length > 0" class="photo-display-grid">
                    <div
                        v-for="(img, idx) in currentRecord.images"
                        :key="idx"
                        class="photo-thumb"
                        @click="openZoom(img)"
                        title="點擊放大檢視照片"
                    >
                        <img :src="img" alt="NW 照片" />
                        <span class="zoom-badge">🔍 點擊放大</span>
                    </div>
                </div>
                <div v-else class="photo-placeholder-box">
                    <div style="font-size: 24px; margin-bottom: 4px;">📷</div>
                    <div style="font-size: 11px; color: #94a3b8;">本次評估無附加影像檔案</div>
                </div>

                <!-- 詳細資訊卡 (DL-082, DL-100) -->
                <div class="info-card">
                    <div class="info-row">
                        <strong class="info-lbl">部位：</strong>
                        <span class="info-val">{{ parsedInfo.locations || '—' }}</span>
                    </div>
                    <div class="info-row">
                        <strong class="info-lbl">症狀：</strong>
                        <span class="info-val">{{ parsedInfo.symptoms || '—' }}</span>
                    </div>
                    <div v-if="parsedInfo.note" class="info-row">
                        <strong class="info-lbl">備註：</strong>
                        <span class="info-val note-val">{{ parsedInfo.note }}</span>
                    </div>
                    <div class="info-row nurse-time-row">
                        <strong class="info-lbl">護理師：</strong>
                        <span class="info-nurse">
                            {{ currentRecord?.nurse_name || '楚心瑜' }} ・ {{ currentRecord?.time_str || currentRecord?.time || '—' }}
                        </span>
                    </div>
                </div>

                <!-- 底部操作列 -->
                <div class="mbtn-row" style="margin-top: 14px;">
                    <button class="mbtn sec" @click="close">關閉</button>
                    <button class="mbtn pri" @click="handleOpenCreate">
                        ➕ 新增 Nurse Watching
                    </button>
                </div>
            </div>
        </div>

        <!-- 📷 照片放大彈窗 (DL-082 modal-nw-zoom) -->
        <div v-if="zoomImage" class="zoom-overlay" @click="zoomImage = null">
            <div class="zoom-modal" @click.stop>
                <div class="modal-hdr">
                    <span class="hdr-title"><i class="ti ti-photo"></i> 照片放大</span>
                    <button class="modal-x" @click="zoomImage = null">✕</button>
                </div>
                <div class="zoom-img-box">
                    <img :src="zoomImage" alt="放大照片" />
                </div>
                <div class="zoom-caption">
                    {{ parsedInfo.locations || '' }} {{ parsedInfo.symptoms ? '・ ' + parsedInfo.symptoms : '' }} ・ {{ currentRecord?.nurse_name || '楚心瑜' }} {{ currentRecord?.time_str || '' }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({
    modelValue: Boolean,
    patient: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "open-create"]);
const store = useDialysisStore();

const currentIndex = ref(0);
const zoomImage = ref(null);

const records = computed(() => {
    return store.nurseWatchings || [];
});

const currentRecord = computed(() => {
    if (records.value.length === 0) return null;
    return records.value[currentIndex.value] || records.value[0];
});

const currentDateLabel = computed(() => {
    if (!currentRecord.value) return "尚無記錄";
    return currentRecord.value.date || currentRecord.value.time?.substring(0, 10) || "—";
});

// 解析部位、症狀與備註
const parsedInfo = computed(() => {
    const raw = currentRecord.value?.content || "";
    if (!raw) return { locations: "", symptoms: "", note: "" };

    const locKeywords = ["上肢", "前臂", "下肢", "大腿", "小腿", "腳掌", "廔管穿刺點", "左腳", "右腳", "左足底", "腳趾", "足底"];
    const symKeywords = ["紅腫", "滲血", "水腫", "疼痛", "硬結", "感染"];

    // 若包含標準冒號分割
    if (raw.includes("部位:") || raw.includes("症狀:") || raw.includes("備註:")) {
        const locMatch = raw.match(/部位[:：]\s*([^|；\n]+)/);
        const symMatch = raw.match(/症狀[:：]\s*([^|；\n]+)/);
        const noteMatch = raw.match(/備註[:：]\s*([^|；\n]+)/);
        return {
            locations: locMatch ? locMatch[1].trim() : "",
            symptoms: symMatch ? symMatch[1].trim() : "",
            note: noteMatch ? noteMatch[1].trim() : "",
        };
    }

    const matchedLocs = [];
    const matchedSyms = [];
    let remaining = raw;

    for (const loc of locKeywords) {
        if (remaining.includes(loc)) {
            matchedLocs.push(loc);
            remaining = remaining.replace(new RegExp(loc, "g"), "");
        }
    }

    for (const sym of symKeywords) {
        if (remaining.includes(sym)) {
            matchedSyms.push(sym);
            remaining = remaining.replace(new RegExp(sym, "g"), "");
        }
    }

    const cleanedNote = remaining.replace(/[,，、|；\s]+/g, " ").trim();

    return {
        locations: matchedLocs.length > 0 ? matchedLocs.join("、") : (matchedSyms.length === 0 && !cleanedNote ? raw : "—"),
        symptoms: matchedSyms.length > 0 ? matchedSyms.join("、") : "—",
        note: cleanedNote || "",
    };
});

watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen) {
            currentIndex.value = 0;
            zoomImage.value = null;
            const targetPatient = props.patient || store.currentPatient;
            if (targetPatient?.id) {
                await store.fetchNurseWatchings(targetPatient.id);
            }
        }
    }
);

const openZoom = (img) => {
    zoomImage.value = img;
};

const handleOpenCreate = () => {
    emit("update:modelValue", false);
    emit("open-create");
};

const close = () => {
    emit("update:modelValue", false);
};
</script>

<style scoped>
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-overlay.open {
    display: flex;
}

.modal {
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    width: 92%;
    max-width: 540px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-sizing: border-box;
}

.modal-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
}

.hdr-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.modal-x {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 16px;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: color 0.15s;
}

.modal-x:hover {
    color: #0f172a;
    background: #f1f5f9;
}

/* 空狀態 */
.empty-state {
    padding: 30px 10px;
    text-align: center;
}

.empty-icon {
    font-size: 36px;
    margin-bottom: 8px;
}

.empty-text {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

/* 日期切換列 */
.date-nav-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin-bottom: 14px;
}

.nav-arrow-btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 4px 12px;
    cursor: pointer;
    font-size: 13px;
    color: #334155;
    transition: all 0.15s;
}

.nav-arrow-btn:hover:not(:disabled) {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}

.nav-arrow-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.nav-center {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.nav-date-label {
    font-size: 14px;
    font-weight: 700;
    color: #0f766e;
}

.nav-counter {
    font-size: 10px;
    color: #94a3b8;
}

/* 傷口照片顯示 */
.photo-display-grid {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.photo-thumb {
    flex: 1;
    min-width: 110px;
    max-width: 240px;
    aspect-ratio: 4 / 3;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: transform 0.15s, box-shadow 0.15s;
}

.photo-thumb:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.photo-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.zoom-badge {
    position: absolute;
    bottom: 4px;
    right: 4px;
    background: rgba(15, 23, 42, 0.7);
    color: white;
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 4px;
    backdrop-filter: blur(2px);
}

.photo-placeholder-box {
    background: #f8fafc;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    margin-bottom: 12px;
}

/* 詳細資訊卡 */
.info-card {
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 12px 14px;
    font-size: 13.5px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    line-height: 1.6;
}

.info-row {
    display: flex;
    align-items: flex-start;
}

.info-lbl {
    color: #475569;
    min-width: 60px;
    flex-shrink: 0;
    font-weight: 600;
}

.info-val {
    color: #1e293b;
    word-break: break-all;
}

.note-val {
    color: #475569;
}

.nurse-time-row {
    margin-top: 6px;
    padding-top: 8px;
    border-top: 1px dashed #e2e8f0;
}

.info-nurse {
    color: #0f766e;
    font-weight: 600;
}

/* 按鈕樣式 */
.mbtn-row {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.mbtn {
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    border: none;
}

.mbtn.sec {
    background: #f1f5f9;
    color: #475569;
}

.mbtn.sec:hover {
    background: #e2e8f0;
}

.mbtn.pri {
    background: #7c3aed;
    color: white;
}

.mbtn.pri:hover {
    background: #6d28d9;
}

/* 放大檢視 Overlay */
.zoom-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.75);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.zoom-modal {
    background: white;
    border-radius: 12px;
    padding: 16px;
    width: 90%;
    max-width: 560px;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
}

.zoom-img-box {
    background: #0f172a;
    border-radius: 8px;
    max-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 8px;
}

.zoom-img-box img {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.zoom-caption {
    font-size: 11px;
    color: #475569;
    text-align: center;
}
</style>

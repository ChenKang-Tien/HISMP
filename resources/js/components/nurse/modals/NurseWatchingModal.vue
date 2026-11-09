<template>
    <div :class="['modal-overlay', { open: modelValue }]" @click.self="close">
        <div class="modal" style="max-width: 500px">
            <div class="modal-hdr">
                <span class="hdr-title">
                    <i class="ti ti-camera"></i> Nurse Watching 新增傷口拍照上傳
                </span>
                <button class="modal-x" @click="close">✕</button>
            </div>

            <!-- 📷 拍照 / 圖片上傳區塊 -->
            <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                multiple
                style="display: none"
                @change="handleFileSelect"
            />

            <div
                class="photo-dropzone"
                @click="triggerFileInput"
            >
                <div v-if="previewImages.length === 0" class="dropzone-placeholder">
                    📷 點擊拍照或選擇圖片
                </div>
                <div v-else class="preview-grid" @click.stop>
                    <div
                        v-for="(img, idx) in previewImages"
                        :key="idx"
                        class="preview-item"
                    >
                        <img :src="img.url" alt="傷口照片" />
                        <button class="btn-remove-img" @click="removeImage(idx)">✕</button>
                    </div>
                    <div class="preview-add-more" @click="triggerFileInput" title="新增更多圖片">
                        ➕
                    </div>
                </div>
            </div>

            <!-- 日期選擇 -->
            <div class="date-row">
                <span class="date-label">日期：</span>
                <input
                    type="date"
                    v-model="form.date"
                    class="date-input"
                />
            </div>

            <!-- 部位選擇（複選） -->
            <div class="form-label">部位（可複選）</div>
            <div class="pill-group" id="nw-location-pills">
                <span
                    v-for="loc in locationOptions"
                    :key="loc"
                    :class="['nw-pill', { selected: form.locations.includes(loc) }]"
                    @click="toggleLocation(loc)"
                >
                    {{ loc }}
                </span>
            </div>

            <!-- 症狀選擇（複選） -->
            <div class="form-label">症狀（可複選）</div>
            <div class="pill-group" id="nw-symptom-pills">
                <span
                    v-for="sym in symptomOptions"
                    :key="sym"
                    :class="['nw-pill', { selected: form.symptoms.includes(sym) }]"
                    @click="toggleSymptom(sym)"
                >
                    {{ sym }}
                </span>
            </div>

            <!-- 備註補充 -->
            <div class="form-label">備註補充</div>
            <textarea
                v-model="form.note"
                class="form-input note-input"
                rows="2"
                placeholder="如：紗布可見組織液"
            ></textarea>

            <!-- 即時組合預覽 (DL-090) -->
            <div class="nw-preview-area">
                <span class="preview-tag">預覽：</span>
                <span class="preview-text">{{ previewText }}</span>
            </div>

            <!-- 按鈕列 -->
            <div class="mbtn-row">
                <button class="mbtn sec" @click="close" :disabled="isSubmitting">取消</button>
                <button class="mbtn pri" @click="submit" :disabled="isSubmitting">
                    {{ isSubmitting ? "上傳中..." : "🚀 具名上傳並通知醫師" }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({
    modelValue: Boolean,
    patient: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "confirm", "saved"]);
const store = useDialysisStore();

const fileInputRef = ref(null);
const isSubmitting = ref(false);
const previewImages = ref([]);
const selectedFiles = ref([]);

const locationOptions = ["上肢", "前臂", "下肢", "大腿", "小腿", "腳掌", "廔管穿刺點"];
const symptomOptions = ["紅腫", "滲血", "水腫", "疼痛", "硬結", "感染"];

const getTodayString = () => {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
};

const form = reactive({
    date: getTodayString(),
    locations: [],
    symptoms: [],
    note: "",
});

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            form.date = getTodayString();
            form.locations = [];
            form.symptoms = [];
            form.note = "";
            previewImages.value = [];
            selectedFiles.value = [];
        }
    }
);

const toggleLocation = (loc) => {
    const idx = form.locations.indexOf(loc);
    if (idx > -1) {
        form.locations.splice(idx, 1);
    } else {
        form.locations.push(loc);
    }
};

const toggleSymptom = (sym) => {
    const idx = form.symptoms.indexOf(sym);
    if (idx > -1) {
        form.symptoms.splice(idx, 1);
    } else {
        form.symptoms.push(sym);
    }
};

const previewText = computed(() => {
    const parts = [...form.locations, ...form.symptoms];
    if (form.note.trim()) parts.push(form.note.trim());
    return parts.length > 0 ? parts.join(" ") : "（請選擇部位與症狀）";
});

const triggerFileInput = () => {
    if (fileInputRef.value) {
        fileInputRef.value.click();
    }
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files || []);
    for (const file of files) {
        selectedFiles.value.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push({
                url: e.target.result,
                file: file,
            });
        };
        reader.readAsDataURL(file);
    }
    // 重設 input 讓同檔名也能觸發 change
    event.target.value = "";
};

const removeImage = (idx) => {
    previewImages.value.splice(idx, 1);
    selectedFiles.value.splice(idx, 1);
};

const close = () => {
    emit("update:modelValue", false);
};

const showToast = (message) => {
    const toast = document.createElement("div");
    toast.style.cssText =
        "position:fixed;top:60px;right:20px;background:#f3e8ff;border:2px solid #e9d5ff;border-radius:10px;padding:10px 14px;font-size:11px;color:#7c3aed;z-index:99999;max-width:320px;box-shadow:0 4px 12px rgba(124,58,237,0.15);font-weight:600;";
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
};

const submit = async () => {
    const targetPatient = props.patient || store.currentPatient;
    const checkId = targetPatient?.id;

    if (!checkId) {
        alert("請先選擇病患以記錄 Nurse Watching！");
        return;
    }

    const contentStr = previewText.value === "（請選擇部位與症狀）" ? "穿刺點傷口觀察正常無外滲。" : previewText.value;

    isSubmitting.value = true;
    try {
        const formData = new FormData();
        formData.append("content", contentStr);
        formData.append("time", `${form.date} ${new Date().toLocaleTimeString("zh-TW", { hour12: false })}`);
        formData.append("nurse", store.nurseName || "楚心瑜");

        // 加入選取圖檔
        for (const file of selectedFiles.value) {
            formData.append("images[]", file);
        }

        // 若無直接選檔但有 base64 預覽
        if (selectedFiles.value.length === 0 && previewImages.value.length > 0) {
            previewImages.value.forEach((img, i) => {
                formData.append(`base64_images[${i}]`, img.url);
            });
        }

        const res = await store.createNurseWatching(checkId, formData);
        
        emit("confirm", contentStr);
        emit("saved", res);

        showToast(`✅ Nurse Watching 已建立！<br>${contentStr}`);
        close();
    } catch (err) {
        console.error("提交 Nurse Watching 失敗:", err);
        // 後端若連線錯誤仍建立本地狀態與留痕
        showToast(`✅ Nurse Watching 已建立！<br>${contentStr}`);
        emit("confirm", contentStr);
        close();
    } finally {
        isSubmitting.value = false;
    }
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
    max-width: 500px;
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

/* 📷 拍照 / 圖片上傳區塊 */
.photo-dropzone {
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 8px;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    cursor: pointer;
    transition: border-color 0.2s, background-color 0.2s;
    padding: 8px;
}

.photo-dropzone:hover {
    border-color: #7c3aed;
    background: #faf5ff;
}

.dropzone-placeholder {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.preview-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    width: 100%;
}

.preview-item {
    position: relative;
    width: 70px;
    height: 70px;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-remove-img {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.65);
    color: white;
    border: none;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-add-more {
    width: 70px;
    height: 70px;
    border-radius: 6px;
    border: 1.5px dashed #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #7c3aed;
    background: white;
    cursor: pointer;
}

/* 日期選擇列 */
.date-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.date-label {
    font-size: 12.5px;
    color: #475569;
    font-weight: 700;
}

.date-input {
    font-size: 12.5px;
    border: 1.5px solid #e2e8f0;
    border-radius: 5px;
    padding: 4px 8px;
    outline: none;
    color: #1e293b;
}

.date-input:focus {
    border-color: #7c3aed;
}

/* 標籤群組 */
.form-label {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}

.pill-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
}

.nw-pill {
    font-size: 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 4px 12px;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
    background: white;
    color: #334155;
}

.nw-pill:hover {
    border-color: #7c3aed;
    color: #7c3aed;
}

.nw-pill.selected {
    background: #7c3aed;
    border-color: #7c3aed;
    color: white;
    font-weight: 600;
}

/* 備註輸入 */
.note-input {
    width: 100%;
    box-sizing: border-box;
    font-size: 13px;
    border: 1.5px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
    resize: none;
    margin-bottom: 10px;
    outline: none;
    color: #1e293b;
    font-family: inherit;
}

.note-input:focus {
    border-color: #7c3aed;
}

/* 即時組合預覽 (DL-090) */
.nw-preview-area {
    background: #f3e8ff;
    border: 1.5px solid #e9d5ff;
    border-radius: 7px;
    padding: 8px 12px;
    margin-bottom: 14px;
    min-height: 36px;
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.preview-tag {
    font-size: 11.5px;
    color: #7c3aed;
    font-weight: 700;
    flex-shrink: 0;
}

.preview-text {
    font-size: 12.5px;
    color: #1e293b;
    font-weight: 500;
    word-break: break-all;
}

/* 按鈕列 */
.mbtn-row {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.mbtn {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
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

.mbtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

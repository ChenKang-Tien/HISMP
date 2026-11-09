<template>
    <div
        v-if="modelValue"
        class="modal-overlay open"
        @click.self="closeModal"
    >
        <div class="emg-modal">
            <div class="emg-hdr">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-alert-triangle" style="font-size: 18px;"></i>
                    <span>🚨 緊急醫療處置作業 ・ 全院急救廣播中</span>
                </div>
                <button class="emg-close-btn" @click="closeModal" title="暫時收合（急救狀態持續中）">✕</button>
            </div>

            <div class="emg-body">
                <div class="emg-patient-banner" v-if="patientName">
                    <span style="font-weight: 700; color: #cc0000;">⚠️ 急救對象：{{ patientName }}（{{ bedNo ? '床號 ' + bedNo : '' }}）</span>
                </div>

                <div class="emg-timer-label">急救作業計時</div>
                <div class="emg-timer" id="emg-timer">{{ formattedTime }}</div>

                <div class="emg-checklist-title">臨床急救查核清單（逐項點擊確認）：</div>
                <div
                    v-for="(item, idx) in checklist"
                    :key="idx"
                    class="emg-chk"
                    :class="{ done: item.done }"
                    @click="item.done = !item.done"
                >
                    <div class="emg-chk-c">{{ item.done ? '✓' : '' }}</div>
                    <span style="font-weight: 600;">{{ item.text }}</span>
                    <span v-if="item.done" style="margin-left: auto; font-size: 11px; color: #15803d; font-weight: 700;">{{ item.statusText || '已就位' }}</span>
                </div>

                <!-- 護理記錄輸入框（即時記錄急救過程、給藥或生命徵象備註） -->
                <div class="emg-note-section">
                    <div class="emg-note-title">
                        <i class="ti ti-notes" style="font-size: 13px;"></i>
                        <span>急救處置護理記錄備註（連動病歷留痕）：</span>
                    </div>
                    <textarea
                        v-model="nurseNote"
                        class="emg-note-input"
                        placeholder="請輸入急救當下處置記錄（例如：給予 Bosmin 1 amp IV push、氣管插管順利、急救人員交接事項等）..."
                        rows="3"
                    ></textarea>
                </div>

                <div class="mbtn-row">
                    <button class="mbtn sec" @click="closeModal">
                        暫時收合面板（急救持續中）
                    </button>
                    <button class="mbtn danger" @click="handleEndEmergency">
                        🛑 結束急救並解除危急狀態
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onUnmounted, computed } from "vue";

const props = defineProps({
    modelValue: Boolean,
    patientName: String,
    bedNo: [String, Number],
});

const emit = defineEmits(["update:modelValue", "end-emergency"]);

const checklist = ref([
    { text: "通知主治醫師與值班團隊", done: true },
    { text: "備妥急救車與電擊去顫器 (AED/Defibrillator)", done: true },
    { text: "建立通暢靜脈輸液通路", done: false },
    { text: "連續監測心電圖與生命徵象 (ECG / NIBP / SpO2)", done: false },
    { text: "備妥氣管插管 (Endo) 與甦醒球設備", done: false },
    { text: "送上救護車", done: false, statusText: "已送出" },
]);

const nurseNote = ref("");
const seconds = ref(0);
let timer = null;

const formattedTime = computed(() => {
    const mins = Math.floor(seconds.value / 60)
        .toString()
        .padStart(2, "0");
    const secs = (seconds.value % 60).toString().padStart(2, "0");
    return `${mins}:${secs}`;
});

const startTimer = () => {
    if (!timer) {
        timer = setInterval(() => {
            seconds.value++;
        }, 1000);
    }
};

const stopTimer = () => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
};

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            // 如果原本沒在計時，重設並啟動
            if (seconds.value === 0) {
                startTimer();
            }
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    stopTimer();
});

const closeModal = () => {
    emit("update:modelValue", false);
};

const handleEndEmergency = () => {
    if (!confirm("確定要結束本次急救處置作業，並解除 EMERGENCY 危急狀態嗎？")) {
        return;
    }
    const duration = formattedTime.value;
    const doneItems = checklist.value.filter((c) => c.done).map((c) => c.text);
    const note = nurseNote.value.trim();
    stopTimer();
    seconds.value = 0;
    emit("end-emergency", { duration, items: doneItems, note });
    nurseNote.value = "";
    emit("update:modelValue", false);
};
</script>

<style scoped>
.modal-overlay {
    display: flex;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(2px);
    z-index: 2500;
    align-items: center;
    justify-content: center;
}
.emg-modal {
    background: #ffffff;
    border-radius: 12px;
    width: 92%;
    max-width: 480px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), 0 0 0 4px rgba(255, 0, 0, 0.25);
    border: 3px solid #ff0000;
}
.emg-hdr {
    background: linear-gradient(90deg, #e60000, #ff1a1a);
    color: #ffffff;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    font-size: 14px;
}
.emg-close-btn {
    background: rgba(0, 0, 0, 0.2);
    border: none;
    color: #ffffff;
    width: 26px;
    height: 26px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.emg-close-btn:hover {
    background: rgba(0, 0, 0, 0.35);
}
.emg-body {
    padding: 16px 18px;
    overflow-y: auto;
}
.emg-patient-banner {
    background: #ffe5e5;
    border: 1.5px solid #ff4d4d;
    border-radius: 6px;
    padding: 6px 10px;
    margin-bottom: 12px;
    font-size: 13px;
    text-align: center;
}
.emg-timer-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-align: center;
}
.emg-timer {
    font-size: 38px;
    font-weight: 800;
    color: #e60000;
    text-align: center;
    margin: 2px 0 14px 0;
    letter-spacing: 2px;
    font-family: monospace;
}
.emg-checklist-title {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 8px;
}
.emg-chk {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    margin-bottom: 6px;
    cursor: pointer;
    font-size: 12.5px;
    color: #1e293b;
    background: #ffffff;
    transition: all 0.15s ease;
}
.emg-chk:hover {
    background: #f8fafc;
}
.emg-chk.done {
    background: #f0fdf4;
    border-color: #86efac;
    color: #14532d;
}
.emg-chk-c {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 700;
}
.emg-chk.done .emg-chk-c {
    background: #16a34a;
    border-color: #16a34a;
    color: #ffffff;
}
.emg-note-section {
    margin-top: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 10px;
}
.emg-note-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 5px;
}
.emg-note-input {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 12px;
    color: #1e293b;
    outline: none;
    font-family: inherit;
    resize: vertical;
    background: #ffffff;
    transition: all 0.15s ease;
}
.emg-note-input:focus {
    border-color: #e60000;
    box-shadow: 0 0 0 2px rgba(230, 0, 0, 0.2);
}

.mbtn-row {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    justify-content: flex-end;
}
.mbtn {
    border: none;
    border-radius: 6px;
    padding: 8px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
}
.mbtn.sec {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}
.mbtn.sec:hover {
    background: #e2e8f0;
}
.mbtn.danger {
    background: #e60000;
    color: #ffffff;
}
.mbtn.danger:hover {
    background: #b30000;
}
</style>

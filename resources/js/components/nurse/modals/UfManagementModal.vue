<template>
    <div v-if="modelValue" class="modal-mask" @click.self="closeModal">
        <div class="modal-container border-amber-bd">
            <!-- 標頭區 -->
            <div class="modal-header text-amber">
                <span class="font-700">💧 實際超濾調水量（UF）校正</span>
                <button class="close-x" @click="closeModal">×</button>
            </div>

            <!-- 軀殼內文 -->
            <div class="modal-body">
                <p class="meta-desc">
                    此數值通常由透析機（DL-093）自動連線帶入。若臨床需要人工干預超濾脫水量，請在此進行具名微調。
                </p>

                <div class="input-form-group">
                    <label class="font-700 text-slate"
                        >今日實際調水量 (kg)</label
                    >
                    <input
                        type="number"
                        step="0.1"
                        v-model.number="localUf"
                        placeholder="請輸入實際調水量"
                        class="v24-styled-input focus-amber"
                    />
                </div>

                <!-- 數據對照系統 -->
                <div
                    class="formula-preview-zone bg-amber-lt border-amber-bd"
                    style="font-size: 11px"
                >
                    <div style="display: flex; justify-content: space-between">
                        <span>💡 系統建議應調水 (目標)：</span>
                        <span class="font-700 text-amber"
                            >{{
                                store.targetUF > 0
                                    ? store.targetUF.toFixed(1)
                                    : "0.0"
                            }}
                            kg</span
                        >
                    </div>
                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            margin-top: 4px;
                            color: #64748b;
                        "
                    >
                        <span>當前乾體重設定值：</span>
                        <span>{{ store.dryWeight || "—" }} kg</span>
                    </div>
                </div>
            </div>

            <!-- 底部操作列 -->
            <div class="modal-footer">
                <button class="btn-cancel" @click="closeModal">取消</button>
                <button class="btn-confirm bg-amber-dk" @click="handleSave">
                    確認調整
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({
    modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue"]);
const store = useDialysisStore();

const localUf = ref(3.5);

watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            localUf.value = store.actualUfWeight || 3.5;
        }
    },
);

const closeModal = () => {
    emit("update:modelValue", false);
};

const handleSave = () => {
    // 覆寫大腦數值
    store.actualUfWeight = localUf.value;
    closeModal();
};
</script>

<style scoped>
.modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-container {
    width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    border: 2px solid transparent;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-header {
    padding: 12px 15px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
}
.close-x {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #94a3b8;
}
.modal-body {
    padding: 15px;
    font-size: 11px;
}
.meta-desc {
    color: #64748b;
    margin-bottom: 12px;
    line-height: 1.4;
}
.v24-styled-input {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-top: 4px;
    box-sizing: border-box;
}
.focus-amber:focus {
    outline: none;
    border-color: #fde68a;
    box-shadow: 0 0 0 3px rgba(253, 230, 138, 0.4);
}
.formula-preview-zone {
    margin-top: 15px;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid;
}
.modal-footer {
    padding: 10px 15px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.btn-cancel {
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 5px 12px;
    font-size: 11px;
    cursor: pointer;
    color: #475569;
}
.btn-confirm {
    border: none;
    border-radius: 6px;
    padding: 5px 14px;
    font-size: 11px;
    cursor: pointer;
    color: white;
    font-weight: 700;
}
.bg-amber-lt {
    background-color: #fffbeb;
}
.border-amber-bd {
    border-color: #fde68a;
}
.text-amber {
    color: #b45309;
}
.bg-amber-dk {
    background-color: #b45309;
}
.text-slate {
    color: #475569;
}
.font-700 {
    font-weight: 700;
}
</style>

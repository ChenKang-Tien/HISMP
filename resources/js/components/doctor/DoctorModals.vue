<template>
    <div v-if="store.currentPatient">
        <div
            class="overlay"
            :class="{ open: store.modals['history'] }"
            @click.self="store.closeModal('history')"
        >
            <div class="modal" style="max-width: 500px">
                <button
                    class="modal-close"
                    @click="store.closeModal('history')"
                >
                    ✕
                </button>
                <div class="modal-title">📅 選擇巡床歷史區間</div>
                <input
                    type="date"
                    v-model="store.visitDate"
                    class="form-input"
                />
                <div class="modal-btns">
                    <button class="btn-ok" @click="store.closeModal('history')">
                        確認切換
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['drywt'] }"
            @click.self="store.closeModal('drywt')"
        >
            <div class="modal" style="max-width: 420px">
                <button class="modal-close" @click="store.closeModal('drywt')">
                    ✕
                </button>
                <div class="modal-title">⚖️ 乾體重調整</div>
                <div class="modal-sub">
                    目前：{{ store.currentPatient.dryWeight || "59.5" }} kg
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-label">新乾體重（kg）</div>
                        <input
                            class="form-input"
                            type="number"
                            step="0.1"
                            v-model="store.modalForm.dryWeight"
                        />
                    </div>
                </div>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('drywt')"
                    >
                        取消
                    </button>
                    <button class="btn-ok" @click="store.updateDryWeight()">
                        確認更新
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-param-edit'] }"
            @click.self="store.closeModal('modal-param-edit')"
        >
            <div class="modal" style="max-width: 440px">
                <button
                    class="modal-close"
                    @click="store.closeModal('modal-param-edit')"
                >
                    ✕
                </button>
                <div class="modal-title">✏️ 修改透析醫囑參數</div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-label">透析器 Dialyzer</div>
                        <input
                            class="form-input"
                            v-model="store.modalForm.dialyzer"
                        />
                    </div>
                </div>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('modal-param-edit')"
                    >
                        取消
                    </button>
                    <button
                        class="btn-ok"
                        @click="store.saveParamEditWithTrace()"
                    >
                        確認更新
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-drug-long-edit'] }"
            @click.self="store.closeModal('modal-drug-long-edit')"
        >
            <div class="modal" style="max-width: 460px">
                <button
                    class="modal-close"
                    @click="store.closeModal('modal-drug-long-edit')"
                >
                    ✕
                </button>
                <div class="modal-title">✏️ 修改長期用藥</div>
                <div
                    v-for="(drug, idx) in store.currentPatient.longTermDrugs ||
                    []"
                    :key="idx"
                    style="
                        background: var(--slate-ul);
                        border-radius: 8px;
                        padding: 8px 10px;
                        margin-bottom: 6px;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    "
                >
                    <span
                        style="flex: 1"
                        :style="
                            drug.deleted
                                ? 'text-decoration:line-through;color:var(--slate-lt);'
                                : ''
                        "
                    >
                        {{ drug.name }}
                        <small
                            v-if="drug.deleted"
                            style="color: var(--red); display: block"
                            >{{ drug.deleteTrace }}</small
                        >
                    </span>
                    <button
                        v-if="!drug.deleted"
                        class="hw-btn del"
                        @click="store.deleteDrugItem(idx)"
                    >
                        🗑️
                    </button>
                </div>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('modal-drug-long-edit')"
                    >
                        關閉
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-drug-change-confirm'] }"
        >
            <div class="modal" style="max-width: 500px">
                <div
                    style="
                        font-size: 14px;
                        font-weight: 700;
                        margin-bottom: 12px;
                    "
                >
                    ⚠️ 藥物停用留痕確認
                </div>
                <div
                    style="
                        background: var(--red-lt);
                        padding: 10px;
                        border-radius: 8px;
                        color: var(--red);
                        font-weight: 700;
                        text-decoration: line-through;
                        margin-bottom: 12px;
                    "
                >
                    {{ store.drugChangePending.oldTxt }}
                </div>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('modal-drug-change-confirm')"
                    >
                        取消
                    </button>
                    <button class="btn-ok" @click="store.confirmDrugChange()">
                        確認變更
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-ward-last'] }"
            @click.self="store.closeModal('modal-ward-last')"
        >
            <div class="modal" style="max-width: 520px">
                <button
                    class="modal-close"
                    @click="store.closeModal('modal-ward-last')"
                >
                    ✕
                </button>
                <div class="modal-title">📋 上次巡床記錄預覽</div>
                <div
                    class="confirm-section"
                    style="
                        background: var(--slate-ul);
                        padding: 10px;
                        border-radius: 8px;
                    "
                >
                    <p><strong>S:</strong> 頭暈、稍感噁心</p>
                    <p><strong>A:</strong> 透析中低血壓前兆</p>
                </div>
                <div class="modal-btns">
                    <button class="btn-ok" @click="store.carryLastWardItems()">
                        帶入並標記【延續】
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-normal-confirm'] }"
            @click.self="store.closeModal('modal-normal-confirm')"
        >
            <div class="modal" style="max-width: 380px">
                <button
                    class="modal-close"
                    @click="store.closeModal('modal-normal-confirm')"
                >
                    ✕
                </button>
                <div class="modal-title">✅ 確認完成巡床</div>
                <div
                    style="font-size: 13px; text-align: center; padding: 10px 0"
                >
                    今日無醫囑異動，確認巡床完成？
                </div>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('modal-normal-confirm')"
                    >
                        取消
                    </button>
                    <button class="btn-ok" @click="store.submitNormalVisit()">
                        確認送出
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-ward-summary'] }"
            @click.self="store.closeModal('modal-ward-summary')"
        >
            <div class="modal" style="max-width: 420px">
                <button
                    class="modal-close"
                    @click="store.closeModal('modal-ward-summary')"
                >
                    ✕
                </button>
                <div class="modal-title">✅ 本次巡床摘要確認</div>
                <div
                    style="
                        font-size: 12px;
                        color: var(--amber);
                        margin-bottom: 12px;
                    "
                >
                    ⚠️ 送出後起算 24 小時內可修改，超過即永久上鎖。
                </div>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('modal-ward-summary')"
                    >
                        返回修改
                    </button>
                    <button class="btn-ok" @click="store.submitWardRound()">
                        確認送出
                    </button>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['chart'] }"
            @click.self="store.closeModal('chart')"
        >
            <div class="modal" style="max-width: 420px">
                <button class="modal-close" @click="store.closeModal('chart')">
                    ✕
                </button>
                <div class="modal-title">
                    📈 歷史趨勢圖：{{ store.chartModalTitle }}
                </div>
                <div class="chart-area">
                    <div class="chart-col">
                        <div class="chart-bar" style="height: 55px"></div>
                        <div class="chart-clabel">5/4</div>
                    </div>
                    <div class="chart-col">
                        <div class="chart-bar" style="height: 68px"></div>
                        <div class="chart-clabel">5/8</div>
                    </div>
                    <div class="chart-col">
                        <div class="chart-bar hi" style="height: 72px"></div>
                        <div class="chart-clabel">5/23</div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="overlay"
            :class="{ open: store.modals['modal-pn-edit'] }"
            @click.self="store.closeModal('modal-pn-edit')"
        >
            <div class="modal" style="max-width: 500px">
                <button
                    class="modal-close"
                    @click="store.closeModal('modal-pn-edit')"
                >
                    ✕
                </button>
                <div class="modal-title">📝 Progress Note</div>
                <textarea
                    v-model="store.pnForm.content"
                    style="
                        width: 100%;
                        height: 140px;
                        padding: 8px;
                        border-radius: 6px;
                        border: 1px solid var(--border);
                    "
                ></textarea>
                <div class="modal-btns">
                    <button
                        class="btn-cancel"
                        @click="store.closeModal('modal-pn-edit')"
                    >
                        取消
                    </button>
                    <button class="btn-ok" @click="store.savePNEdit()">
                        儲存
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useDoctorStore } from "@/store/doctorStore";
const store = useDoctorStore();
</script>

<style scoped>
.overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}
.overlay.open {
    display: flex;
}
.modal {
    background: white;
    border-radius: 12px;
    padding: 18px;
    max-height: 84vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}
.modal-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--red);
    border: none;
    color: white;
    width: 26px;
    height: 26px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 12px;
}
.modal-sub {
    font-size: 11px;
    color: var(--slate-lt);
    margin-bottom: 10px;
}
.form-row {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}
.form-col {
    flex: 1;
}
.form-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--slate);
    margin-bottom: 4px;
}
.form-input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 7px 8px;
    font-size: 13px;
    outline: none;
}
.modal-btns {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 14px;
}
.btn-cancel {
    background: var(--slate-ul);
    border: 1px solid var(--border);
    color: var(--slate);
    border-radius: 6px;
    padding: 7px 18px;
    cursor: pointer;
}
.btn-ok {
    background: var(--green);
    border: none;
    color: white;
    border-radius: 6px;
    padding: 7px 18px;
    font-weight: 700;
    cursor: pointer;
}
.chart-area {
    background: var(--slate-ul);
    border-radius: 8px;
    height: 90px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 8px;
    padding: 10px;
}
.chart-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.chart-bar {
    background: var(--pri2);
    border-radius: 3px 3px 0 0;
    width: 22px;
    opacity: 0.7;
}
.chart-bar.hi {
    opacity: 1;
    background: var(--pri);
}
.chart-clabel {
    font-size: 9px;
    color: var(--slate-lt);
}
</style>

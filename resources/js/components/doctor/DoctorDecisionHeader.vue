<template>
    <div class="dr-top">
        <div class="pt-title-block">
            <span class="pt-title">
                {{ currentPatient.name }}
                <span class="bed-sub">床 {{ currentPatient.bedNo }}</span>
            </span>
            <span class="pt-meta-text">
                {{ currentPatient.chartNo }} · {{ currentPatient.meta }}
            </span>
        </div>

        <div class="dr-status-tags">
            <span
                v-if="currentPatient.status === 'crit'"
                class="v39-badge badge-danger"
            >
                <i class="ti ti-alert-triangle"></i> 高風險危急
            </span>
            <span
                v-else-if="currentPatient.status === 'wait'"
                class="v39-badge badge-warning"
            >
                <i class="ti ti-clock"></i> 待處置
            </span>
            <span v-else class="v39-badge badge-info">
                <i class="ti ti-activity"></i> 透析監護中
            </span>

            <span
                :class="[
                    'v39-badge',
                    currentPatient.status === 'visited'
                        ? 'badge-success'
                        : 'badge-secondary',
                ]"
            >
                <i
                    :class="[
                        'ti',
                        currentPatient.status === 'visited'
                            ? 'ti-lock'
                            : 'ti-user-check',
                    ]"
                ></i>
                {{
                    currentPatient.status === "visited"
                        ? "已完成巡床 (已上鎖)"
                        : "今日待巡視"
                }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useDoctorStore } from "@/store/doctorStore";

const store = useDoctorStore();

// 動態計算當前大腦選中的病患
const currentPatient = computed(() => store.currentPatient);
</script>

<style scoped>
.dr-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
}

.pt-title-block {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.pt-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a; /* 貼合 V39 深色標題字 */
}

.bed-sub {
    font-size: 14px;
    color: var(--slate-lt);
    font-weight: 400;
    margin-left: 2px;
}

.pt-meta-text {
    font-size: 12px;
    color: var(--slate-lt);
    font-family: monospace, var(--font);
}

.dr-status-tags {
    display: flex;
    gap: 6px;
    align-items: center;
}

/* 📥 100% 繼承自 V39 原生語意化 Badge 規格與色彩樣式 */
.v39-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid transparent;
}

.badge-danger {
    background: var(--red-lt);
    color: var(--red);
    border-color: var(--red-bd);
}

.badge-warning {
    background: var(--amber-lt);
    color: var(--amber);
    border-color: var(--amber-bd);
}

.badge-info {
    background: var(--pri-lt);
    color: var(--pri);
    border-color: var(--pri-bd);
}

.badge-success {
    background: var(--green-lt);
    color: var(--green);
    border-color: var(--green-bd);
}

.badge-secondary {
    background: var(--slate-ul);
    color: var(--slate);
    border-color: var(--border);
}
</style>

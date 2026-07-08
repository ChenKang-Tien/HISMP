<template>
    <div class="offsign-tab-wrapper">
        <!-- 三格大方塊生理數值填寫盤 (完全複製原稿) -->
        <div class="bp2-grid">
            <div class="bp-box">
                <div class="bpl">透後臥姿血壓</div>
                <div class="bpv">{{ offVitals.lieBP || "—" }}</div>
                <button class="bp-edit" @click="fillBP('lie')">填寫</button>
            </div>
            <div class="bp-box">
                <div class="bpl">透後坐姿血壓</div>
                <div class="bpv">{{ offVitals.sitBP || "—" }}</div>
                <button class="bp-edit" @click="fillBP('sit')">填寫</button>
            </div>
            <div
                class="bp-box"
                style="background: #f0fdfa; border-color: #99f6e4"
            >
                <div class="bpl" style="color: #0f766e">透後體重</div>
                <div class="bpv" style="color: #0f766e">
                    {{
                        store.postRawWeight ? store.postRawWeight + " kg" : "—"
                    }}
                </div>
                <button class="bp-edit" @click="fillPostWeight">填寫</button>
            </div>
        </div>

        <!-- 檢核查對行：圓圈點選行 (完全對齊原稿) -->
        <div
            class="chk"
            :class="{ done: checks.weight }"
            @click="checks.weight = !checks.weight"
        >
            <div class="chk-c">{{ checks.weight ? "✓" : "" }}</div>
            <span>透後過磅體重確認完成</span>
        </div>
        <div
            class="chk"
            :class="{ done: checks.clot }"
            @click="checks.clot = !checks.clot"
        >
            <div class="chk-c">{{ checks.clot ? "✓" : "" }}</div>
            <span>AK / Chamber 凝血核實完成（Clear）</span>
        </div>
        <div
            class="chk"
            :class="{ done: checks.supply }"
            @click="checks.supply = !checks.supply"
        >
            <div class="chk-c">{{ checks.supply ? "✓" : "" }}</div>
            <span>今日下機消耗醫材清點具名核對完成</span>
        </div>
        <div
            class="chk"
            :class="{ done: checks.needle }"
            @click="checks.needle = !checks.needle"
        >
            <div class="chk-c">{{ checks.needle ? "✓" : "" }}</div>
            <span>針孔壓迫止血安全確認（無滲血）</span>
        </div>

        <!-- 透後靜脈給藥 (完全對齊原稿 --purple 針劑區) -->
        <div class="post-drug-zone">
            <div class="post-drug-hdr">
                <i class="ti ti-needle"></i>透後靜脈給藥（醫囑派發）
            </div>
            <div class="post-drug-panel">
                <div class="post-drug-body">
                    <div class="p-drug-info">
                        <div class="p-drug-name">Epogin 4000u</div>
                        <div class="p-drug-meta">SC / 依醫囑 ・ 張院醫師</div>
                    </div>
                    <div class="p-drug-actions" v-if="!drugExecStatus">
                        <button
                            class="p-btn-exec"
                            @click="drugExecStatus = '✅ 楚心瑜 13:05 已執行'"
                        >
                            ☑ 執行
                        </button>
                        <button
                            class="p-btn-refuse"
                            @click="drugExecStatus = '❌ 楚心瑜 13:06 拒打'"
                        >
                            ✗ 拒打
                        </button>
                    </div>
                    <div class="p-drug-status-text" v-else>
                        {{ drugExecStatus }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 終端下機簽章鈕 -->
        <button class="offsign-main-btn" @click="executeFinalOffSign">
            🛑 執行下機簽章 Off-Sign 總大核對
        </button>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useDialysisStore } from "@/store/useNurseStore";

const store = useDialysisStore();

const offVitals = ref({ lieBP: "", sitBP: "" });
const drugExecStatus = ref("");
const checks = ref({
    weight: false,
    clot: false,
    supply: false,
    needle: false,
});

const fillBP = (type) => {
    const v = prompt("請輸入透後血壓（如 12880）：", "128/80");
    if (v) {
        if (type === "lie") offVitals.value.lieBP = v;
        if (type === "sit") offVitals.value.sitBP = v;
    }
};

const fillPostWeight = () => {
    const w = prompt("請輸入透後體重（如 7650 代表 76.50）：", "76.5");
    if (w) {
        store.postRawWeight = parseFloat(w);
        checks.value.weight = true;
    }
};

const executeFinalOffSign = () => {
    if (
        !offVitals.value.lieBP ||
        !offVitals.value.sitBP ||
        !store.postRawWeight
    ) {
        alert("❌ 歸檔攔截：請先完成透後血壓與體重填寫！");
        return;
    }
    store.addNursingRecord(
        `[下機作業] 安全下機歸檔。透後臥姿血壓:${offVitals.value.lieBP}, 坐姿血壓:${offVitals.value.sitBP}, 體重:${store.postRawWeight}kg。下機清查無誤。`,
    );
    alert("🏁 歸檔成功！本班次病歷已正式封條鎖定。");
};
</script>

<style scoped>
.bp2-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 5px;
    margin-bottom: 8px;
}
.bp-box {
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    padding: 7px;
    text-align: center;
    background: white;
}
.bp-box .bpl {
    font-size: 10px;
    color: #94a3b8;
    margin-bottom: 2px;
}
.bp-box .bpv {
    font-size: 15px;
    font-weight: 700;
    color: #0f766e;
}
.bp-box .bp-edit {
    font-size: 10px;
    color: #0f766e;
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 4px;
    padding: 2px 7px;
    cursor: pointer;
    margin-top: 4px;
    font-family: inherit;
}

.chk {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    margin-bottom: 4px;
    cursor: pointer;
    font-size: 12px;
    background: white;
    user-select: none;
}
.chk.done {
    background: #f0fdf4;
    border-color: #86efac;
}
.chk-c {
    width: 19px;
    height: 19px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 10px;
    font-weight: 700;
    color: white;
}
.chk.done .chk-c {
    background: #16a34a;
    border-color: #16a34a;
}

.post-drug-zone {
    margin-top: 8px;
    border-top: 1.5px solid #e2e8f0;
    padding-top: 7px;
}
.post-drug-hdr {
    font-size: 11px;
    font-weight: 700;
    color: #7c3aed;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.post-drug-panel {
    background: #f5f3ff;
    border: 1.5px solid #ddd6fe;
    border-radius: 7px;
    padding: 6px 10px;
}
.post-drug-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.p-drug-name {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
}
.p-drug-meta {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 1px;
}
.p-drug-actions {
    display: flex;
    gap: 4px;
}
.p-btn-exec {
    font-size: 10px;
    background: #f0fdf4;
    color: #16a34a;
    border: 1.5px solid #86efac;
    border-radius: 4px;
    padding: 2px 7px;
    cursor: pointer;
    font-weight: 700;
}
.p-btn-refuse {
    font-size: 10px;
    background: #fef2f2;
    color: #b91c1c;
    border: 1.5px solid #fecaca;
    border-radius: 4px;
    padding: 2px 7px;
    cursor: pointer;
    font-weight: 700;
}
.p-drug-status-text {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
}

.offsign-main-btn {
    width: 100%;
    background: #b91c1c;
    color: white;
    border: none;
    border-radius: 9px;
    padding: 12px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    box-shadow: 0 4px 6px -1px rgba(185, 28, 28, 0.15);
}
.offsign-main-btn:hover {
    background: #991b1b;
}
</style>

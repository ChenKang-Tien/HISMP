import { defineStore } from "pinia";
import axios from "axios";

// 建立 Axios 實例，整合 Sanctum / Passport 登入守衛發放的 Bearer Token
const api = axios.create({
    baseURL: "/api/v1",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem("hismp_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

export const useDialysisStore = defineStore("dialysis", {
    state: () => ({
        nurseName: "楚心瑜",
        currentShift: "午班 11:00~17:00",
        searchQuery: "",

        // UI 佈局與頁籤
        isLeftDrawerOpen: false,
        isFixedColCollapsed: false,
        activeTab: 0,

        // 當前選中病患的業務快照
        currentPatient: {
            bed: "—",
            name: "請選擇病患",
            mr: "—",
            hasFSOrder: false,
        },

        // 🟢 RESTful 動態分流名單緩衝池
        patientGroups: [], // 今日在院病患名單 (GET /dialysis/patients)
        absentPatientsList: [], // 請假住院固定池 (POST /absence-leave 搬移結果)
        offsignPatients: [], // 已下機完帳清單

        // ⚖️ 體重、扣重與 HCT 反應式狀態
        preRawWeight: 0,
        postRawWeight: null,
        deductions: [], // 扣重膠囊管理池 (DL-125)
        dryWeight: 0,
        actualUfWeight: null,
        hctLW: 0,
        hctTW: null,
        hctAT: null,

        // 💉 生理徵象與評估
        vsignFilled: false,
        vsignData: { bp: "—", pr: "—", rr: "—", temp: "—", fs: "—" },
        mainSigned: false,
        assessState: {
            vascular: "未評估",
            conscious: "未評估",
            skin: "未評估",
        },

        // 📋 護理記錄病歷不滅時間軸
        nursingRecords: [],
        autoSaveTime: "—",
    }),

    getters: {
        deductionTotal(state) {
            return state.deductions.reduce((sum, d) => sum + d.weight, 0);
        },
        preAdjWeight(state) {
            return state.preRawWeight
                ? parseFloat(
                      (state.preRawWeight - this.deductionTotal).toFixed(2),
                  )
                : null;
        },
        targetUF(state) {
            if (!this.preAdjWeight) return 0;
            return Math.max(
                0,
                parseFloat((this.preAdjWeight - state.dryWeight).toFixed(2)),
            );
        },
        // 🔍 RESTful 輔助：前端對大盤資料進行多維度即時過濾（床號/姓名/MR），減輕後端資料庫撈取負擔 (DL-150)
        filteredGroups(state) {
            const query = state.searchQuery.trim().toLowerCase();
            if (!query) return state.patientGroups;
            return state.patientGroups
                .map((g) => ({
                    ...g,
                    patients: g.patients.filter(
                        (pt) =>
                            pt.name.toLowerCase().includes(query) ||
                            pt.mr.toLowerCase().includes(query) ||
                            pt.bed.toLowerCase().includes(query),
                    ),
                }))
                .filter((g) => g.patients.length > 0);
        },
    },

    actions: {
        // 🟢 RESTful [GET] - 載入今日當班次所有病患大盤
        async fetchTodayShiftPatients() {
            try {
                const res = await api.get("/dialysis/patients", {
                    params: { shift: "noon" },
                });
                console.log(res.data);

                this.patientGroups = res.data.active_groups;
                this.absentPatientsList = res.data.absent_patients;
                this.offsignPatients = res.data.offsign_patients;
            } catch (err) {
                console.error("API 連線失敗狀態:", err);

                // 🚀 火箭測試攔截：如果後端吐回我們寫的 500 斷點，立刻在瀏覽器彈出對話框
                if (
                    err.response &&
                    err.response.data &&
                    err.response.data.message
                ) {
                    alert(err.response.data.message);
                } else {
                    alert(
                        "⚠️ 網路連線失敗，請檢查 Laravel Route 是否有開通 API 大腦開關！",
                    );
                }
            }
        },

        // 🟢 RESTful [GET] - 切換病患，拉取該病患本班次特定醫療資源
        async selectPatient(patient) {
            this.currentPatient = patient;
            this.vsignFilled = false;

            try {
                const res = await api.get(
                    `/patients/${patient.mr}/dialysis-cases/current`,
                );

                // 解構後端傳回的標準洗腎資訊大盤
                this.preRawWeight = res.data.weight_info.pre_raw_weight;
                this.dryWeight = res.data.weight_info.dry_weight;
                this.deductions = res.data.weight_info.deductions;
                this.vsignData = res.data.vitals;
                this.vsignFilled = res.data.vitals_filled;
                this.assessState = res.data.assess;
                this.nursingRecords = res.data.nursing_records;
                this.autoSaveTime = res.data.last_autosave;
            } catch (err) {
                console.warn("後端對應資源節點暫無回應，採用本地緩衝資料。");
            }
        },

        // 🟢 RESTful [POST] - 為特定病患建立一筆新護理記錄
        async addNursingRecord(content) {
            try {
                const res = await api.post(
                    `/patients/${this.currentPatient.mr}/nursing-records`,
                    { content },
                );
                // 後端持久化成功後，將回傳的帶有實體自增 ID 的完整紀錄推入最前線
                this.nursingRecords.unshift(res.data.record);
                this.autoSaveTime = res.data.record.time;
            } catch (err) {
                console.error("病歷寫入失敗:", err);
            }
        },

        // 🟢 RESTful [PUT] - 具名修改病歷記錄 (加線留痕相容)
        async editNursingRecord(id, newContent) {
            try {
                const res = await api.put(`/nursing-records/${id}`, {
                    content: newContent,
                });
                const idx = this.nursingRecords.findIndex((r) => r.id === id);
                if (idx !== -1) {
                    // 後端會回傳：「楚心瑜 11:20 修正：[舊值] $\rightarrow$ [新值]」的留痕字串
                    this.nursingRecords[idx] = res.data.record;
                }
            } catch (err) {
                console.error("修改失敗:", err);
            }
        },

        // 🟢 RESTful [DELETE] - 註銷病歷記錄 (法律加線留痕，打上 isDeleted 標籤)
        async deleteNursingRecord(id) {
            try {
                const res = await api.delete(`/nursing-records/${id}`);
                const rec = this.nursingRecords.find((r) => r.id === id);
                if (rec) {
                    rec.isDeleted = true;
                    rec.deletedMeta = res.data.deleted_meta; // 「〈楚心瑜 11:58 刪除〉」
                }
            } catch (err) {
                console.error("刪除失敗:", err);
            }
        },

        // 🟢 RESTful [POST] - 核發離院假單，流轉病患狀態 (DL-116/122)
        async processPatientAbsence(pt, status, note) {
            try {
                await api.post(`/patients/${pt.mr}/absence-leave`, {
                    status,
                    note,
                });
                // 假單流轉完成後，直接重新向後端索取大盤，病患會被重新分流到請假住院池
                await this.fetchTodayShiftPatients();
                return true;
            } catch (err) {
                console.error("假單傳送失敗:", err);
                return false;
            }
        },

        // 🟢 RESTful [POST] - 更新病患目標脫水量
        async updatePatientUfGoal(mr, payload) {
            try {
                // 此處對接 API 端點，假設後端已開通此路由
                await api.post(`/patients/${mr}/uf-goal`, payload);
                // 更新當前狀態或重新獲取資料
                this.addNursingRecord(`[系統通知] 設定目標脫水量為 ${payload.uf_goal} kg。備註：${payload.note}`);
                return true;
            } catch (err) {
                console.error("UF 目標設定失敗:", err);
                alert("設定失敗，請確認後端路由是否已部署。");
                return false;
            }
        },
    },
});

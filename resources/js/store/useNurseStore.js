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
        api: api, // 導出 api 實例
        nurseName: "楚心瑜",
        currentShift: "午班 11:00~17:00",
        currentShiftFilter: "午班", // 💡 新增班別過濾狀態
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

        // 🧪 API 加載狀態
        loading: false,
        error: null,

        // ⚖️ 體重、扣重與 HCT 反應式狀態
        preRawWeight: 0,
        postRawWeight: 0, // 初始化為 0
        preDeductions: [], // 透前扣重管理池
        postDeductions: [], // 透後扣重管理池
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
        preDeductionTotal(state) {
            console.log("DEBUG: preDeductions", JSON.parse(JSON.stringify(state.preDeductions)));
            const list = state.preDeductions || [];
            return list.reduce((sum, d) => {
                if (!d || typeof d.weight === 'undefined') {
                    console.error("DEBUG: Invalid item found", d);
                    return sum;
                }
                return sum + (Number(d.weight) || 0);
            }, 0);
        },
        postDeductionTotal(state) {
            console.log("DEBUG: postDeductions", JSON.parse(JSON.stringify(state.postDeductions)));
            const list = state.postDeductions || [];
            return list.reduce((sum, d) => {
                if (!d || typeof d.weight === 'undefined') {
                    console.error("DEBUG: Invalid item found", d);
                    return sum;
                }
                return sum + (Number(d.weight) || 0);
            }, 0);
        },
        // 將原有的 getter 內容替換為簡單屬性存取，將複雜計算移至 calculateWeights Action 中
        preAdjWeight: (state) => state.preAdjWeight,
        postAdjWeight: (state) => state.postAdjWeight,
        targetUF(state) {
            if (this.preAdjWeight === null) return 0;
            return Math.max(
                0,
                parseFloat((this.preAdjWeight - (state.dryWeight || 0)).toFixed(2)),
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
                const shiftMap = {
                    早班: "morning",
                    午班: "noon",
                    晚班: "night",
                    全院: "all",
                };
                const res = await api.get("/dialysis/patients", {
                    params: {
                        shift: shiftMap[this.currentShiftFilter] || "noon",
                    },
                });

                console.log("🟢 [API Patient Response]:", res.data);

                this.patientGroups =
                    res.data.active_groups ||
                    this.generateMockGroups(this.currentShiftFilter);
                this.absentPatientsList = res.data.absent_patients || [];
                this.offsignPatients = res.data.offsign_patients || [];
            } catch (err) {
                console.error("🔴 [API Error]:", err);
                this.patientGroups = this.generateMockGroups(
                    this.currentShiftFilter,
                );
                this.absentPatientsList = [];
                this.offsignPatients = [];
            }
        },

        // 🟢 內部輔助：生成各班別模擬假資料
        generateMockGroups(shift) {
            const mockData = {
                早班: [
                    {
                        name: "A 組・早班專用護理師",
                        color: "#0f766e",
                        isMine: true,
                        patients: [
                            {
                                bed: "01",
                                mr: "MR-M-01",
                                name: "早班-張小華",
                                statusText: "☀️ 早班 ・ 透析準備",
                            },
                            {
                                bed: "02",
                                mr: "MR-M-02",
                                name: "早班-李大明",
                                statusText: "☀️ 早班 ・ 透析中",
                            },
                        ],
                    },
                    {
                        name: "B 組・早班備援護理師",
                        color: "#059669",
                        isMine: false,
                        patients: [
                            {
                                bed: "05",
                                mr: "MR-M-05",
                                name: "早班-王小明",
                                statusText: "☀️ 早班 ・ 已透析 1h",
                            },
                        ],
                    },
                ],
                午班: [
                    {
                        name: "A 組・楚心瑜護理師",
                        color: "#0f766e",
                        isMine: true,
                        patients: [
                            {
                                bed: "01",
                                mr: "MR9876543",
                                name: "薛玉鳳",
                                statusText: "🟢 午班 ・ 透析中 ・ 已透 2h 24m",
                            },
                            {
                                bed: "02",
                                mr: "MR223344",
                                name: "林*芳",
                                statusText: "🔴 午班 ・ 血壓偏高",
                            },
                        ],
                    },
                    {
                        name: "C 組・午班實習護理師",
                        color: "#d97706",
                        isMine: false,
                        patients: [
                            {
                                bed: "09",
                                mr: "MR-N-09",
                                name: "午班-陳小美",
                                statusText: "🟢 午班 ・ 脫水中",
                            },
                        ],
                    },
                ],
                晚班: [
                    {
                        name: "B 組・晚班輪值護理師",
                        color: "#7c3aed",
                        isMine: true,
                        patients: [
                            {
                                bed: "08",
                                mr: "MR-E-08",
                                name: "晚班-黃大偉",
                                statusText: "🌙 晚班 ・ 準備下機",
                            },
                            {
                                bed: "10",
                                mr: "MR-E-10",
                                name: "晚班-趙小莉",
                                statusText: "🌙 晚班 ・ 透析開始",
                            },
                        ],
                    },
                ],
                全院: [
                    {
                        name: "全院綜整",
                        color: "#64748b",
                        isMine: true,
                        patients: [
                            {
                                bed: "All",
                                mr: "SYS",
                                name: "全院監控模式",
                                statusText: "🌐 系統整合中",
                            },
                        ],
                    },
                ],
            };
            return mockData[shift] || mockData["午班"];
        },

        // 🟢 修正版：計算權重 Action
        calculateWeights() {
            const pre = parseFloat(this.preRawWeight || 0);
            const post = parseFloat(this.postRawWeight || 0);
            const preTotal = (this.preDeductions || []).reduce((sum, d) => sum + (d.weight || 0), 0);
            const postTotal = (this.postDeductions || []).reduce((sum, d) => sum + (d.weight || 0), 0);
            
            this.preAdjWeight = pre ? parseFloat((pre - preTotal).toFixed(2)) : null;
            this.postAdjWeight = post ? parseFloat((post - postTotal).toFixed(2)) : null;
        },

        async setShiftFilter(shift) {
            this.currentShiftFilter = shift;
            await this.fetchTodayShiftPatients();
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
                this.preRawWeight = res.data.weight_info.pre_raw_weight || 0;
                this.postRawWeight = res.data.weight_info.post_raw_weight || 0;
                this.dryWeight = res.data.weight_info.dry_weight || 0;
                this.preDeductions = (res.data.weight_info.pre_deductions && Array.isArray(res.data.weight_info.pre_deductions)) 
                    ? res.data.weight_info.pre_deductions 
                    : [];
                this.postDeductions = (res.data.weight_info.post_deductions && Array.isArray(res.data.weight_info.post_deductions)) 
                    ? res.data.weight_info.post_deductions 
                    : [];
                this.vsignData = res.data.vitals || {};
                this.vsignFilled = !!res.data.vitals_filled;
                this.assessState = res.data.assess || {};
                this.nursingRecords = res.data.nursing_records || [];
                this.autoSaveTime = res.data.last_autosave || "—";
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

        // 🟢 RESTful [GET] - 獲取病患詳細電子病歷區塊資訊 (DL-119)
        async fetchPatientDetails(mr, type) {
            try {
                // 對應後端路由：GET /api/v1/patients/{mr}/details/{type}
                const res = await api.get(`/patients/${mr}/details/${type}`);
                return res.data;
            } catch (err) {
                console.warn(`[API] 獲取 ${type} 資料失敗，返回模擬資料。`);
                return this.generateMockDetail(type);
            }
        },

        generateMockDetail(type) {
            const mocks = {
                basicInfo: {
                    name: "薛玉鳳",
                    age: 72,
                    bloodType: "O+",
                    allergies: ["Penicillin"],
                },
                orderSheet: [
                    { id: 1, name: "EPO 4000U", freq: "Weekly" },
                    { id: 2, name: "Iron IV", freq: "Monthly" },
                ],
                vascular: {
                    type: "AVF",
                    site: "Left forearm",
                    lastCheck: "2026-08-01",
                },
                anemia: { hgb: 9.1, target: 11.0, iron: "Normal" },
                lab: { K: 6.1, HGB: 9.1, Creatinine: 8.5 },
                longterm: [{ id: 1, desc: "降壓藥", usage: "Daily" }],
            };
            return mocks[type] || { message: "無相關資料" };
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
                this.addNursingRecord(
                    `[系統通知] 設定目標脫水量為 ${payload.uf_goal} kg。備註：${payload.note}`,
                );
                return true;
            } catch (err) {
                console.error("UF 目標設定失敗:", err);
                alert("設定失敗，請確認後端路由是否已部署。");
                return false;
            }
        },

        // 🟢 RESTful [POST] - 記錄臨床事件
        async reportIncident(mr, type) {
            try {
                await api.post(`/patients/${mr}/incidents`, { type });
                this.addNursingRecord(`[臨床事件] ${type} 已記錄。`);
                return true;
            } catch (err) {
                console.error("事件記錄失敗:", err);
                return false;
            }
        },

        // 🟢 RESTful [GET] - 獲取可排班清單 (護理師 + 組別)
        async fetchShiftOptions() {
            try {
                const res = await api.get("/nursing/shift-options");
                return res.data;
            } catch (err) {
                console.warn("[API] 獲取排班選項失敗，使用模擬資料。");
                return {
                    nurses: [
                        { id: 1, name: "楚心瑜" },
                        { id: 2, name: "王曉明" },
                    ],
                    groups: [
                        { id: 1, name: "A 組" },
                        { id: 2, name: "B 組" },
                    ],
                };
            }
        },

        // 🟢 RESTful [POST] - 提交護理排班
        async saveShift(payload) {
            try {
                await api.post("/nursing/shifts", payload);
                this.addNursingRecord(
                    `[排班設定] ${payload.date} 排班: ${payload.nurse_id} 至 ${payload.group_id}。`,
                );
                return true;
            } catch (err) {
                console.error("排班儲存失敗:", err);
                return false;
            }
        },

        // 🟢 RESTful [POST] - 更新病患體重數據與自動留痕
        async updatePatientWeights(mr, weightData) {
            try {
                await api.post(`/patients/${mr}/weights`, {
                    pre: weightData.pre,
                    post: weightData.post,
                    note: weightData.note,
                });
            
                this.addNursingRecord(
                    `[體重校正] 透前: ${weightData.pre ?? 0}kg, 透後: ${weightData.post ?? 0}kg。備註: ${weightData.note || "無"}`
                );
                return true;
            } catch (err) {
                console.error("體重更新失敗:", err);
                return false;
            }
        },

        // 🟢 獨立同步扣重項目
        async syncWeightAdjustments(mr) {
            try {
                if (!mr || mr === "—") return false;
            
                // 修正：始終發送請求，即使是空陣列也要讓後端處理刪除
                const payload = {
                    items: [
                        ...this.preDeductions
                            .filter(d => d.item_id)
                            .map(d => ({ item_id: d.item_id, weight: d.weight, category: 'pre' })),
                        ...this.postDeductions
                            .filter(d => d.item_id)
                            .map(d => ({ item_id: d.item_id, weight: d.weight, category: 'post' }))
                    ]
                };

                await api.post(`/patients/${mr}/weight-adjustments`, payload);
                return true;
            } catch (err) {
                console.error("扣重同步失敗:", err);
                return false;
            }
        },

        // 🟢 RESTful [GET] - 取得明日領料清單
        async fetchSupplyList() {
            try {
                const res = await api.get("/nursing/supply-tmr");
                return res.data;
            } catch (err) {
                console.warn("[API] 獲取領料清單失敗，使用模擬資料。");
                return {
                    items: [
                        {
                            id: 1,
                            name: "FX80 Classix 人工腎臟",
                            count: 12,
                            unit: "組",
                        },
                        {
                            id: 2,
                            name: "Heparin 1000u/ml",
                            count: 24,
                            unit: "支",
                        },
                    ],
                    isLocked: false,
                };
            }
        },

        // 🟢 RESTful [POST] - 鎖定領料清單
        async lockSupplyList() {
            try {
                await api.post(`/nursing/supply-tmr/lock`);
                alert("🔒 領料清單已鎖定並同步至庫房系統。");
                return true;
            } catch (err) {
                console.error("領料鎖定失敗:", err);
                return false;
            }
        },
    },
});

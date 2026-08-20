import { defineStore } from "pinia";
import axios from "axios";

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
        api: api,
        nurseName: "楚心瑜",
        currentShift: "午班 11:00~17:00",
        currentShiftFilter: "午班",
        searchQuery: "",
        isLeftDrawerOpen: false,
        isFixedColCollapsed: false,
        activeTab: 0,
        currentPatient: {
            bed: "—",
            name: "請選擇病患",
            mr: "—",
            hasFSOrder: false,
        },
        patientGroups: [],
        absentPatientsList: [],
        offsignPatients: [],
        loading: false,
        error: null,
        preRawWeight: 0,
        postRawWeight: 0,
        preDeductions: [],
        postDeductions: [],
        dryWeight: 0,
        actualUfWeight: null,
        hctLW: 0,
        hctTW: null,
        hctAT: null,
        vsignFilled: false,
        vsignData: { bp: "—", pr: "—", rr: "—", temp: "—", fs: "—" },
        mainSigned: false,
        assessState: {
            vascular: "未評估",
            conscious: "未評估",
            skin: "未評估",
        },
        nursingRecords: [],
        autoSaveTime: "—",
        preDeductionTotal: 0,
        postDeductionTotal: 0,
        preAdjWeight: null,
        postAdjWeight: null,
    }),
    getters: {
        targetUF(state) {
            if (state.preAdjWeight === null) return 0;
            return Math.max(
                0,
                parseFloat(
                    (state.preAdjWeight - (state.dryWeight || 0)).toFixed(2),
                ),
            );
        },
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
        calculateWeights() {
            const pre = parseFloat(this.preRawWeight || 0);
            const post = parseFloat(this.postRawWeight || 0);
            this.preDeductionTotal = (this.preDeductions || []).reduce(
                (sum, d) => sum + (Number(d.weight) || 0),
                0,
            );
            this.postDeductionTotal = (this.postDeductions || []).reduce(
                (sum, d) => sum + (Number(d.weight) || 0),
                0,
            );
            // 修正：實際調水 = 透前真實體重 (pre+preDeduction) - 透後真實體重 (post+postDeduction)
            this.preAdjWeight = pre
                ? parseFloat((pre + this.preDeductionTotal).toFixed(2))
                : null;
            this.postAdjWeight = post
                ? parseFloat((post + this.postDeductionTotal).toFixed(2))
                : null;

            // 更新實際脫水量 (UF)
            if (this.preAdjWeight !== null && this.postAdjWeight !== null) {
                this.actualUfWeight = parseFloat(
                    (this.preAdjWeight - this.postAdjWeight).toFixed(2),
                );
            } else {
                this.actualUfWeight = null;
            }
        },
        async setShiftFilter(shift) {
            this.currentShiftFilter = shift;
            await this.fetchTodayShiftPatients();
        },
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
        generateMockGroups(shift) {
            return [
                { name: "A 組", color: "#0f766e", isMine: true, patients: [] },
            ];
        },
        async selectPatient(patient) {
            this.currentPatient = patient;
            this.nursingRecords = []; // 清空舊記錄
            try {
                // 1. 取得體重與透析基礎資訊
                const res = await api.get(`/dialysis-checks/${patient.id}`);
                const info = res.data?.weight_info || {};
                
                // 更新 store.vsignData
                if (res.data.vitals) {
                    this.vsignData = {
                        sys: res.data.vitals.sys || "",
                        dia: res.data.vitals.dia || "",
                        pr: res.data.vitals.pr || "—",
                        rr: res.data.vitals.rr || "—",
                        temp: res.data.vitals.temp || "—",
                        fs: res.data.vitals.fs || "—",
                    };
                    this.vsignFilled = res.data.vitals_filled;
                }

                // 同步更新病患醫囑狀態
                this.currentPatient.hasFSOrder = res.data.hasFSOrder || false;
                
                this.preRawWeight = parseFloat(info.pre_raw_weight || 0);
                this.postRawWeight = parseFloat(info.post_raw_weight || 0);
                this.dryWeight = parseFloat(info.dry_weight || 0);
                this.preDeductions = Array.isArray(info.pre_deductions)
                    ? info.pre_deductions.map(d => ({ ...d, category: 'pre' }))
                    : [];
                this.postDeductions = Array.isArray(info.post_deductions)
                    ? info.post_deductions.map(d => ({ ...d, category: 'post' }))
                    : [];
                
                // 2. 獲取該次透析的護理記錄
                const recRes = await api.get(`/dialysis-checks/${patient.id}/nursing-records`);
                this.nursingRecords = recRes.data.records || [];
                
                this.calculateWeights();
            } catch (err) {
                console.error("🔴 [Patient Load Error]:", err);
                this.preDeductions = [];
                this.postDeductions = [];
                this.calculateWeights();
            }
        },
        async syncWeightAdjustments(check_id) {
            try {
                const getPureData = (arr) =>
                    (arr || []).map((d) => ({
                        item_id: Number(d.item_id),
                        weight: Number(d.weight),
                        category: d.category,
                    }));
                // 這裡也需要配合路由修改，建議後端 NursingActionController 也一併改為使用 check_id 參數
                const res = await api.post(`/dialysis-checks/${check_id}/weight-adjustments`, {
                    items: [
                        ...getPureData(this.preDeductions),
                        ...getPureData(this.postDeductions),
                    ],
                });
                
                console.log('API Response Debug:', res.data);
                return true;
            } catch (err) {
                if (err.response) {
                    console.error('API Error (422/Other):', err.response.data);
                }
                return false;
            }
        },
        async updatePatientWeights(check_id, data) {
            try {
                await api.post(`/dialysis-checks/${check_id}/weights`, data);
                return true;
            } catch (err) {
                console.error("🔴 [Weight Update Error]:", err);
                return false;
            }
        },
        async updateVitals(check_id, data) {
            try {
                await api.post(`/dialysis-checks/${check_id}/vitals`, data);
                return true;
            } catch (err) {
                console.error("🔴 [Vitals Update Error]:", err);
                throw err; // 拋出給 TabOnSign.vue 處理
            }
        },

        async fetchShiftOptions() {
            try {
                const res = await api.get("/nursing/shift-options");
                return res.data;
            } catch (err) {
                console.error("🔴 [Shift Options Error]:", err);
                return [];
            }
        },
        async fetchSupplyList() {
            try {
                const res = await api.get("/nursing/supply-tmr");
                return res.data;
            } catch (err) {
                console.error("🔴 [Supply List Error]:", err);
                return [];
            }
        },
        async fetchPatientDetails(mr) {
            try {
                const res = await api.get(`/patients/${mr}/dialysis-cases/current`);
                return res.data;
            } catch (err) {
                console.error("🔴 [Patient Details Error]:", err);
                return null;
            }
        },
        async addNursingRecord(content, time = null) {
            try {
                // 改用 check_id 進行請求
                const res = await api.post(`/dialysis-checks/${this.currentPatient.id}/nursing-records`, {
                    content: content,
                    time: time
                });
                if (res.data.success) {
                    this.nursingRecords.unshift(res.data.record);
                }
            } catch (err) {
                console.error("🔴 [Nursing Record Error]:", err);
            }
        },
        async deleteNursingRecord(id) {
            console.log("DEBUG: 準備刪除記錄 ID:", id);
            try {
                const res = await api.delete(`/nursing-records/${id}`);
                console.log("DEBUG: 刪除回應:", res.data);
                const idx = this.nursingRecords.findIndex(r => r.id === id);
                if (idx !== -1) {
                    this.nursingRecords[idx].deleted = true;
                    this.nursingRecords[idx].deletedMeta = '已註銷';
                }
            } catch (err) {
                console.error("🔴 [Delete Record Error]:", err);
            }
        },
    },
});

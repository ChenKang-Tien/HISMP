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
        currentPatient: { bed: "—", name: "請選擇病患", mr: "—", hasFSOrder: false },
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
        assessState: { vascular: "未評估", conscious: "未評估", skin: "未評估" },
        nursingRecords: [],
        autoSaveTime: "—",
        preDeductionTotal: 0,
        postDeductionTotal: 0,
        preAdjWeight: null,
        postAdjWeight: null
    }),
    getters: {
        targetUF(state) {
            if (state.preAdjWeight === null) return 0;
            return Math.max(0, parseFloat((state.preAdjWeight - (state.dryWeight || 0)).toFixed(2)));
        },
        filteredGroups(state) {
            const query = state.searchQuery.trim().toLowerCase();
            if (!query) return state.patientGroups;
            return state.patientGroups
                .map((g) => ({
                    ...g,
                    patients: g.patients.filter((pt) =>
                        pt.name.toLowerCase().includes(query) ||
                        pt.mr.toLowerCase().includes(query) ||
                        pt.bed.toLowerCase().includes(query)
                    ),
                }))
                .filter((g) => g.patients.length > 0);
        },
    },
    actions: {
        calculateWeights() {
            const pre = parseFloat(this.preRawWeight || 0);
            const post = parseFloat(this.postRawWeight || 0);
            this.preDeductionTotal = (this.preDeductions || []).reduce((sum, d) => sum + (Number(d.weight) || 0), 0);
            this.postDeductionTotal = (this.postDeductions || []).reduce((sum, d) => sum + (Number(d.weight) || 0), 0);
            this.preAdjWeight = pre ? parseFloat((pre - this.preDeductionTotal).toFixed(2)) : null;
            this.postAdjWeight = post ? parseFloat((post - this.postDeductionTotal).toFixed(2)) : null;
        },
        async setShiftFilter(shift) {
            this.currentShiftFilter = shift;
            await this.fetchTodayShiftPatients();
        },
        async fetchTodayShiftPatients() {
            try {
                const shiftMap = { 早班: "morning", 午班: "noon", 晚班: "night", 全院: "all" };
                const res = await api.get("/dialysis/patients", { params: { shift: shiftMap[this.currentShiftFilter] || "noon" } });
                this.patientGroups = res.data.active_groups || this.generateMockGroups(this.currentShiftFilter);
                this.absentPatientsList = res.data.absent_patients || [];
                this.offsignPatients = res.data.offsign_patients || [];
            } catch (err) {
                console.error("🔴 [API Error]:", err);
                this.patientGroups = this.generateMockGroups(this.currentShiftFilter);
                this.absentPatientsList = [];
                this.offsignPatients = [];
            }
        },
        generateMockGroups(shift) {
            return [{ name: "A 組", color: "#0f766e", isMine: true, patients: [] }];
        },
        async selectPatient(patient) {
            this.currentPatient = patient;
            try {
                const res = await api.get(`/patients/${patient.mr}/dialysis-cases/current`);
                const info = res.data?.weight_info || {};
                this.preRawWeight = parseFloat(info.pre_raw_weight || 0);
                this.postRawWeight = parseFloat(info.post_raw_weight || 0);
                this.dryWeight = parseFloat(info.dry_weight || 0);
                this.preDeductions = Array.isArray(info.pre_deductions) ? info.pre_deductions : [];
                this.postDeductions = Array.isArray(info.post_deductions) ? info.post_deductions : [];
                this.calculateWeights();
            } catch (err) {
                this.preDeductions = [];
                this.postDeductions = [];
                this.calculateWeights();
            }
        },
        async syncWeightAdjustments(mr) {
            try {
                const getPureData = (arr) => (arr || []).map(d => ({ item_id: Number(d.item_id), weight: Number(d.weight), category: d.category || 'pre' }));
                await api.post(`/patients/${mr}/weight-adjustments`, {
                    items: [...getPureData(this.preDeductions), ...getPureData(this.postDeductions)]
                });
                return true;
            } catch (err) {
                return false;
            }
        }
    }
});

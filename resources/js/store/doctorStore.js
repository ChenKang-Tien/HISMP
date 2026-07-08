import { defineStore } from "pinia";

export const useDoctorStore = defineStore("doctor", {
    state: () => ({
        // 全域搜尋與快速過濾
        searchQuery: "",
        currentShift: "午班",
        selectedPatientId: "205",
        isVisitedExpanded: false,

        // ════ V39 新增：UI 控制與表單緩衝狀態 ════
        visitDate: "2026-05-23",
        lowerActiveTab: "tab-order",
        orderSubTab: "op1",
        chartModalTitle: "",

        // 彈窗開關矩陣
        modals: {
            history: false,
            drywt: false,
            "modal-param-edit": false,
            "modal-drug-long-edit": false,
            "modal-drug-change-confirm": false,
            "modal-ward-last": false,
            "modal-normal-confirm": false,
            "modal-ward-summary": false,
            chart: false,
            "modal-pn-edit": false,
        },

        // 表單與異動緩衝器
        modalForm: { dryWeight: 59.5, dialyzer: "" },
        dialysisParamsTrace: { dialyzer: "" },
        soapData: { s: "", a: "" },
        pnForm: { mode: "new", targetId: null, content: "" },
        drugChangePending: { idx: null, oldTxt: "" },

        // 完美還原自 V39 HTML 的完整今日病患清單
        patients: [
            {
                id: "104",
                bedNo: "104",
                bedColor: "blue",
                name: "黃志宏",
                chartNo: "D-0765432",
                meta: "M/63歲",
                tags: ["ESRD", "高血壓"],
                allergies: [],
                status: "dialysis",
                statusText: "透析中 — 第 2 小時",
                progress: 38,
                timeText: "1:31/4:00",
                dryWeight: 62.0,
                dialyzer: "FX80 Classix",
                longTermDrugs: [
                    {
                        name: "Calcium Carbonate 1# TID PO",
                        deleted: false,
                        deleteTrace: "",
                    },
                ],
                progressNotes: [],
                wtChip: {
                    pre: "63.2kg",
                    preUf: "2.0kg",
                    preAdj: "1.6L",
                    post: "⏳結案後",
                },
                kpiMini: {
                    bp: "118/74",
                    pr: "80",
                    hb: "11.0",
                    uf: "0.8L",
                    ufr: "0.57",
                },
                monitoringRecords: [],
            },
            {
                id: "108",
                bedNo: "108",
                bedColor: "red",
                name: "林阿財",
                chartNo: "D-0984512",
                meta: "M/72歲",
                tags: ["ESRD"],
                allergies: [],
                status: "crit",
                statusText: "危急 — K⁺ 6.8 mEq/L",
                progress: 45,
                timeText: "1:48/4:00",
                dryWeight: 70.0,
                dialyzer: "FX80 Classix",
                longTermDrugs: [],
                progressNotes: [],
                wtChip: {
                    pre: "71.5kg",
                    preUf: "3.0kg",
                    preAdj: "—",
                    post: "—",
                },
                kpiMini: {
                    bp: "168/98",
                    pr: "—",
                    hb: "9.8",
                    uf: "—",
                    ufr: "—",
                    extraLabel: "K⁺",
                    extraVal: "6.8",
                },
                monitoringRecords: [],
            },
            {
                id: "205",
                bedNo: "205",
                bedColor: "blue",
                name: "謝佳萍",
                chartNo: "D-1203847",
                meta: "F/58歲·透析齡 6年2月",
                tags: ["ESRD", "糖尿病腎病"],
                allergies: ["Penicillin", "Sulfa"],
                status: "dialysis",
                statusText: "透析中 — 第 3 小時",
                progress: 72,
                timeText: "3:02/4:00",
                dryWeight: 59.5,
                dialyzer: "FX80 Classix",
                longTermDrugs: [
                    {
                        name: "Calcium Carbonate 1# TID PO",
                        deleted: false,
                        deleteTrace: "",
                    },
                ],
                progressNotes: [
                    {
                        id: 1,
                        date: "2026-05-20 14:32",
                        doctor: "陳建志 醫師",
                        content:
                            "S: 頭暈、稍感噁心。A: 透析中低血壓前兆。P: UFR 降至 0.45 L/hr。",
                        locked: false,
                    },
                    {
                        id: 2,
                        date: "2026-05-17 15:10",
                        doctor: "陳建志 醫師",
                        content: "S: 穩定無不適。Kt/V 1.15（略低）。",
                        locked: true,
                    },
                ],
                wtChip: {
                    pre: "61.8kg",
                    preUf: "2.3kg",
                    preAdj: "1.8L",
                    post: "⏳結案後",
                },
                kpiMini: {
                    bp: "128/78",
                    pr: "—",
                    hb: "10.2",
                    ktv: "1.15",
                    uf: "1.59L",
                },
                // 網格真實數據
                monitoringRecords: [
                    {
                        time: "12:20",
                        label: "透前",
                        bp: "126/80",
                        pr: "82",
                        qb: "—",
                        vp: "—",
                        tmp: "—",
                        uf: "—",
                        ufr: "—",
                        hep: "設定 0.5 / 餘 6.0",
                        qd: "—",
                        cond: "—",
                        temp: "35.5",
                        memo: "—",
                        ak: "—",
                        isAdd: false,
                    },
                    {
                        time: "13:20",
                        label: "第 1 小時",
                        bp: "126/83",
                        pr: "84",
                        qb: "300",
                        vp: "180",
                        tmp: "100",
                        uf: "0.55",
                        ufr: "0.57",
                        hep: "5.5",
                        qd: "500",
                        cond: "140",
                        temp: "35.5",
                        memo: "—",
                        ak: "AK- / 無 / 有 / 100ml",
                        isAdd: false,
                    },
                    {
                        time: "13:55",
                        label: "⚡ 加測",
                        bp: "148/92",
                        pr: "96",
                        qb: "—",
                        vp: "—",
                        tmp: "—",
                        uf: "—",
                        ufr: "—",
                        hep: "—",
                        qd: "—",
                        cond: "—",
                        temp: "—",
                        memo: "UFR↓",
                        ak: "—",
                        isAdd: true,
                        isWarn: true,
                    },
                    {
                        time: "14:20",
                        label: "第 2 小時",
                        bp: "121/82",
                        pr: "90",
                        qb: "300",
                        vp: "160",
                        tmp: "100",
                        uf: "1.22",
                        ufr: "0.57",
                        hep: "5.10",
                        qd: "500",
                        cond: "140",
                        temp: "35.5",
                        memo: "—",
                        ak: "AK- / 無 / 有 / 50ml",
                        isAdd: false,
                    },
                    {
                        time: "15:20",
                        label: "第 3 小時",
                        bp: "129/86",
                        pr: "89",
                        qb: "300",
                        vp: "160",
                        tmp: "100",
                        uf: "1.59",
                        ufr: "0.57",
                        hep: "4.5",
                        qd: "500",
                        cond: "140",
                        temp: "35.5",
                        memo: "—",
                        ak: "AK- / 無 / 有 / 100ml",
                        isCurrentRow: true,
                    },
                ],
            },
            {
                id: "312",
                bedNo: "312",
                bedColor: "amber",
                name: "王大明",
                chartNo: "D-1105633",
                meta: "M/65歲",
                tags: [],
                allergies: [],
                status: "wait",
                statusText: "待上針",
                progress: 0,
                dryWeight: 75.0,
                dialyzer: "FX80 Classix",
                longTermDrugs: [],
                progressNotes: [],
                timeText: "",
                wtChip: {
                    pre: "78.2kg",
                    preUf: "2.5kg",
                    preAdj: "—",
                    post: "—",
                },
                kpiMini: { hb: "11.0", ktv: "1.15" },
                monitoringRecords: [],
            },
            {
                id: "217",
                bedNo: "217",
                bedColor: "gray",
                name: "陳淑芬",
                chartNo: "D-0876421",
                meta: "F/51歲",
                status: "visited",
            },
            {
                id: "220",
                bedNo: "220",
                bedColor: "gray",
                name: "吳美玲",
                chartNo: "D-1034521",
                meta: "F/46歲",
                status: "visited",
            },
        ],
    }),

    getters: {
        currentPatient(state) {
            return (
                state.patients.find((p) => p.id === state.selectedPatientId) ||
                state.patients[2]
            );
        },
        activePatients(state) {
            return state.patients.filter(
                (p) =>
                    p.status !== "visited" &&
                    (state.searchQuery === "" ||
                        p.name.includes(state.searchQuery) ||
                        p.bedNo.includes(state.searchQuery)),
            );
        },
        visitedPatients(state) {
            return state.patients.filter(
                (p) =>
                    p.status === "visited" &&
                    (state.searchQuery === "" ||
                        p.name.includes(state.searchQuery) ||
                        p.bedNo.includes(state.searchQuery)),
            );
        },
    },

    actions: {
        selectPatient(id) {
            this.selectedPatientId = id;
        },
        toggleVisitedSection() {
            this.isVisitedExpanded = !this.isVisitedExpanded;
        },

        // ════ V39 全域功能 Actions ════
        shiftVisitDate(delta) {
            const d = new Date(this.visitDate);
            d.setDate(d.getDate() + delta);
            this.visitDate = d.toISOString().split("T")[0];
        },
        toggleEmergency() {
            if (this.currentPatient) {
                this.currentPatient.status =
                    this.currentPatient.status === "crit" ? "dialysis" : "crit";
            }
        },
        openModal(type) {
            if (type === "drywt")
                this.modalForm.dryWeight = this.currentPatient.dryWeight;
            if (type === "modal-param-edit")
                this.modalForm.dialyzer = this.currentPatient.dialyzer;
            this.modals[type] = true;
        },
        closeModal(type) {
            this.modals[type] = false;
        },

        // DL-176: 乾體重調整
        updateDryWeight() {
            if (this.currentPatient) {
                this.currentPatient.dryWeight = parseFloat(
                    this.modalForm.dryWeight,
                ).toFixed(1);
            }
            this.closeModal("drywt");
        },

        // DL-177: 透析參數異動留痕
        saveParamEditWithTrace() {
            if (
                this.currentPatient &&
                this.modalForm.dialyzer !== this.currentPatient.dialyzer
            ) {
                this.dialysisParamsTrace.dialyzer = `透析器: <span style="color:var(--amber);font-weight:600;">${this.currentPatient.dialyzer} → ${this.modalForm.dialyzer}</span>`;
                this.currentPatient.dialyzer = this.modalForm.dialyzer;
            }
            this.closeModal("modal-param-edit");
        },

        // Fix-D-12/13/15: 藥物加線留痕二次彈窗
        deleteDrugItem(idx) {
            const drug = this.currentPatient.longTermDrugs[idx];
            this.drugChangePending.idx = idx;
            this.drugChangePending.oldTxt = drug.name;
            this.openModal("modal-drug-change-confirm");
        },
        confirmDrugChange() {
            const idx = this.drugChangePending.idx;
            const drug = this.currentPatient.longTermDrugs[idx];
            const now = new Date().toLocaleString("zh-TW", { hour12: false });
            drug.deleted = true;
            drug.deleteTrace = `〈陳建志醫師 ${now} 刪除〉`;
            this.closeModal("modal-drug-change-confirm");
        },

        // DL-180: 帶入上次巡床
        openWardLastPreview() {
            this.openModal("modal-ward-last");
        },
        carryLastWardItems() {
            this.soapData.s = "頭暈、稍感噁心 【延續】";
            this.soapData.a = "透析中低血壓前兆 【延續】";
            this.closeModal("modal-ward-last");
        },

        // 歷史圖表
        showChart(title) {
            this.chartModalTitle = title;
            this.openModal("chart");
        },

        // Progress Notes 新增與編輯
        openPNEdit(mode, note = null) {
            this.pnForm.mode = mode;
            if (mode === "edit" && note) {
                this.pnForm.targetId = note.id;
                this.pnForm.content = note.content;
            } else {
                this.pnForm.targetId = null;
                this.pnForm.content = "";
            }
            this.openModal("modal-pn-edit");
        },
        savePNEdit() {
            if (this.pnForm.mode === "new") {
                this.currentPatient.progressNotes.unshift({
                    id: Date.now(),
                    date: new Date().toLocaleString("zh-TW", { hour12: false }),
                    doctor: "陳建志 醫師",
                    content: this.pnForm.content,
                    locked: false,
                });
            } else {
                const target = this.currentPatient.progressNotes.find(
                    (n) => n.id === this.pnForm.targetId,
                );
                if (target) target.content = this.pnForm.content;
            }
            this.closeModal("modal-pn-edit");
        },

        // 送出功能
        submitNormalVisit() {
            this.currentPatient.status = "visited";
            this.closeModal("modal-normal-confirm");
        },
        submitWardRound() {
            this.currentPatient.status = "visited";
            this.closeModal("modal-ward-summary");
        },
        openPopout(id) {
            alert(`🔍 開啟 [${id}] 的歷史整合視窗`);
        },
    },
});

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
    const token =
        sessionStorage.getItem("hismp_token") ||
        localStorage.getItem("hismp_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            sessionStorage.removeItem("hismp_token");
            sessionStorage.removeItem("hismp_role_id");
            sessionStorage.removeItem("hismp_user_name");
            sessionStorage.removeItem("hismp_role_name");
            sessionStorage.removeItem("hismp_user_id");
            localStorage.removeItem("hismp_token");
            localStorage.removeItem("hismp_role_id");
            localStorage.removeItem("hismp_user_name");
            localStorage.removeItem("hismp_role_name");
            localStorage.removeItem("hismp_user_id");
            if (window.location.pathname !== "/login") {
                window.location.href = "/login";
            }
        }
        return Promise.reject(error);
    },
);

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
        mainNurseName: "",
        mainNurseId: null,
        doubleSigned: false,
        doubleNurseName: "",
        doubleNurseId: null,
        assessState: {
            vascular: "未評估",
            conscious: "未評估",
            skin: "未評估",
        },
        nursingRecords: [],
        nurseWatchings: [],
        ordersPool: [],
        isNWLoading: false,
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
            if (!patient) return;
            this.currentPatient = patient;
            this.nursingRecords = []; // 清空舊記錄
            
            // 防呆：確認有有效的病患 check_id
            if (!patient.id || patient.id === "—" || patient.id === "undefined") {
                console.warn("⚠️ [selectPatient]: 尚未選取具有有效 check_id 的病患，跳過後端請求", patient);
                return;
            }

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

                // 載入簽章狀態 (來自 patient_checks 資料表之 prepare_nurse_id 與 check_nurse_id)
                if (res.data.signs) {
                    this.mainSigned = !!res.data.signs.main_signed;
                    this.mainNurseId = res.data.signs.main_nurse_id;
                    this.mainNurseName = res.data.signs.main_nurse_name || "";
                    this.doubleSigned = !!res.data.signs.double_signed;
                    this.doubleNurseId = res.data.signs.double_nurse_id;
                    this.doubleNurseName = res.data.signs.double_nurse_name || "";
                } else {
                    this.mainSigned = false;
                    this.mainNurseId = null;
                    this.mainNurseName = "";
                    this.doubleSigned = false;
                    this.doubleNurseId = null;
                    this.doubleNurseName = "";
                }

                // 同步更新病患醫囑狀態
                this.currentPatient.hasFSOrder = res.data.hasFSOrder || false;

                // 同步載入當週與上週 HCT (LW / TW / AT)
                if (res.data.hct) {
                    this.hctTW = res.data.hct.tw !== null && res.data.hct.tw !== undefined ? res.data.hct.tw : (patient.hct || null);
                    this.hctLW = res.data.hct.lw !== null && res.data.hct.lw !== undefined ? res.data.hct.lw : (patient.hct ? (parseFloat(patient.hct) - 1.5).toFixed(1) : "—");
                    this.hctAT = res.data.hct.at !== null && res.data.hct.at !== undefined ? res.data.hct.at : null;
                } else {
                    this.hctTW = patient.hct || null;
                    this.hctLW = patient.hct ? (parseFloat(patient.hct) - 1.5).toFixed(1) : "—";
                    this.hctAT = null;
                }

                this.preRawWeight = parseFloat(info.pre_raw_weight || 0);
                this.postRawWeight = parseFloat(info.post_raw_weight || 0);
                this.dryWeight = parseFloat(info.dry_weight || 0);
                this.preDeductions = Array.isArray(info.pre_deductions)
                    ? info.pre_deductions.map((d) => ({
                          ...d,
                          category: "pre",
                      }))
                    : [];
                this.postDeductions = Array.isArray(info.post_deductions)
                    ? info.post_deductions.map((d) => ({
                          ...d,
                          category: "post",
                      }))
                    : [];

                // 3. 獲取該次透析的三大評估
                console.log(
                    "DEBUG: Patient load assess data:",
                    res.data.assess,
                );
                // 3. 獲取該次透析的三大評估
                if (res.data.assess) {
                    const assessData = res.data.assess;

                    // 血管：重建顯示字串，確保跟儲存邏輯一致
                    const rawBits = Array.isArray(assessData.vascular_eval)
                        ? assessData.vascular_eval
                        : (assessData.vascular_eval || "1,0,0,0,0,0,").split(
                              ",",
                          );
                    const vBits = rawBits.slice(0, 6);
                    const vLabels = ["正常", "紅", "腫", "熱", "痛", "滲血"];
                    const vEvalStr =
                        vBits[0] === "1"
                            ? "正常"
                            : vBits
                                  .map((b, i) => (b === "1" ? vLabels[i] : ""))
                                  .filter(Boolean)
                                  .join(",");
                    const vNote = assessData.vascular_note || "";
                    const vResult = `${assessData.vascular_kind || "AVF"}${assessData.vascular_kind === "Permcath" && assessData.permcath_len ? " " + assessData.permcath_len + "cm" : ""} ${assessData.vascular_pos_lr || ""}${assessData.vascular_pos_center || ""} ${vEvalStr}${vNote ? " (" + vNote + ")" : ""}`;

                    // 意識：格式 "索引,備註"
                    const cData = (assessData.conscious || "0,").split(",");
                    const cLabels = [
                        "清醒",
                        "嗜睡",
                        "木僵",
                        "半昏迷",
                        "昏迷",
                        "失智",
                    ];
                    const cIdx = parseInt(cData[0]) || 0;
                    const cNote = assessData.conscious_note || "";
                    const cResult =
                        (cLabels[cIdx] || "清醒") + (cNote ? `(${cNote})` : "");

                    // 皮膚：格式 "索引,部位,大小,備註"
                    const sState = assessData.skin === '1' ? '有傷口(含水泡)' : '完整';
                    const sResult = sState + 
                        (assessData.skin_location || assessData.skin_size 
                            ? `(${assessData.skin_location || ''}${assessData.skin_size ? ' ' + assessData.skin_size + 'cm' : ''})` 
                            : '') + 
                        (assessData.skin_note ? ` (${assessData.skin_note})` : '');

                    this.assessState = {
                        vascular: vResult,
                        conscious: cResult,
                        skin: sResult,
                    };
                } else {
                    this.assessState = {
                        vascular: "正常",
                        conscious: "清醒",
                        skin: "完整",
                    };
                }

                // 2. 獲取該次透析的護理記錄
                const recRes = await api.get(
                    `/dialysis-checks/${patient.id}/nursing-records`,
                );
                this.nursingRecords = recRes.data.records || [];

                // 3. 獲取該次透析的 Nurse Watching 記錄
                try {
                    const nwRes = await api.get(
                        `/dialysis-checks/${patient.id}/nurse-watchings`,
                    );
                    this.nurseWatchings = nwRes.data.records || [];
                    if (this.nurseWatchings.length > 0) {
                        this.currentPatient.hasNW = true;
                    }
                } catch (e) {
                    this.nurseWatchings = [];
                }

                // 4. 獲取該次透析的監測數據
                this.monitoringData = await this.fetchMonitoringData(patient.id);

                // 5. 獲取今日臨時醫囑與用藥處置池
                await this.fetchOrders(patient.id);

                this.calculateWeights();
                } catch (err) {
                console.error("🔴 [Patient Load Error]:", err);
                this.preDeductions = [];
                this.postDeductions = [];
                this.nurseWatchings = [];
                this.ordersPool = [];
                this.calculateWeights();
            }
        },
        async syncWeightAdjustments(check_id) {
            try {
                const targetId = check_id || this.currentPatient?.id;
                if (!targetId || targetId === "—") {
                    console.warn("⚠️ [syncWeightAdjustments]: 無有效的 check_id");
                    return false;
                }
                const getPureData = (arr) =>
                    (arr || [])
                        .filter((d) => d && d.item_id)
                        .map((d) => ({
                            item_id: Number(d.item_id),
                            weight: Number(d.weight),
                            category: d.category,
                        }));
                const res = await api.post(
                    `/dialysis-checks/${targetId}/weight-adjustments`,
                    {
                        items: [
                            ...getPureData(this.preDeductions),
                            ...getPureData(this.postDeductions),
                        ],
                    },
                );

                console.log("API Response Debug:", res.data);
                return true;
            } catch (err) {
                if (err.response) {
                    console.error("API Error (422/Other):", err.response.data);
                }
                return false;
            }
        },
        async updatePatientWeights(check_id, data) {
            try {
                const targetId = check_id || this.currentPatient?.id;
                if (!targetId || targetId === "—") {
                    console.warn("⚠️ [updatePatientWeights]: 無有效的 check_id");
                    return false;
                }
                await api.post(`/dialysis-checks/${targetId}/weights`, data);
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

        async updatePatientUfGoal(identifier, payload) {
            try {
                const targetId = this.currentPatient?.id || identifier;
                if (!targetId || targetId === "—") return false;
                await api.post(`/dialysis-checks/${targetId}/uf-goal`, payload);
                if (payload.uf_goal !== undefined) {
                    this.actualUfWeight = parseFloat(Number(payload.uf_goal).toFixed(2));
                }
                this.addNursingRecord(
                    `[系統通知] 設定目標脫水量為 ${payload.uf_goal} kg。備註：${payload.note || '無'}`,
                );
                return true;
            } catch (err) {
                console.error("🔴 [UF Goal Error]:", err);
                return false;
            }
        },

        async reportIncident(identifier, type, description = null) {
            try {
                const targetId = this.currentPatient?.id || identifier;
                if (!targetId || targetId === "—") {
                    console.warn("⚠️ [reportIncident]: 無有效病患 ID");
                    return false;
                }
                const res = await api.post(`/dialysis-checks/${targetId}/incidents`, {
                    type: type,
                    description: description,
                });

                if (res.data?.success) {
                    if (res.data.nursing_record) {
                        this.nursingRecords.unshift({
                            id: 'inc_' + Date.now(),
                            time: res.data.nursing_record.time,
                            value: res.data.nursing_record.value,
                            nurse: res.data.nursing_record.nurse || this.nurseName || '楚心瑜',
                            deleted: false,
                            deletedMeta: null,
                        });
                    }
                    return true;
                }
                return false;
            } catch (err) {
                console.error("🔴 [Report Incident Error]:", err);
                return false;
            }
        },

        async toggleEmergency(checkId, payload = {}) {
            try {
                const targetId = checkId || this.currentPatient?.id;
                if (!targetId || targetId === "—") return false;

                const res = await api.post(`/dialysis-checks/${targetId}/emergency`, payload);
                if (res.data?.success) {
                    if (res.data.nursing_record) {
                        this.nursingRecords.unshift(res.data.nursing_record);
                    }
                    const isCrit = (res.data.status === 4 || res.data.is_crit);
                    if (this.currentPatient && this.currentPatient.id === targetId) {
                        this.currentPatient.isCrit = isCrit;
                        this.currentPatient.status = isCrit ? 'crit' : 'dialysis';
                        this.currentPatient.statusText = isCrit ? '🚨 EMERGENCY 危急' : '🟢 透析中';
                    }
                    // 同步更新清單中該病患狀態
                    for (const g of this.patientGroups) {
                        for (const p of g.patients) {
                            if (p.id === targetId) {
                                p.isCrit = isCrit;
                                p.status = isCrit ? 'crit' : 'dialysis';
                                p.statusText = isCrit ? '🚨 EMERGENCY 危急' : '🟢 透析中';
                            }
                        }
                    }
                    return res.data;
                }
                return false;
            } catch (err) {
                console.error("🔴 [Toggle Emergency Error]:", err);
                return false;
            }
        },

        async updateHct(identifier, hct, type = "tw") {
            try {
                const targetId = this.currentPatient?.id || identifier;
                if (!targetId || targetId === "—") return false;
                const res = await api.post(`/dialysis-checks/${targetId}/hct`, {
                    hct: hct,
                    type: type,
                });
                if (res.data?.success) {
                    if (type === "at") {
                        this.hctAT = hct;
                    } else {
                        this.hctTW = hct;
                    }
                    if (this.currentPatient) {
                        this.currentPatient.hct = hct;
                    }
                    for (const g of this.patientGroups) {
                        const pt = g.patients?.find((p) => p.id === targetId || p.mr === identifier);
                        if (pt) pt.hct = hct;
                    }
                    if (res.data.nursing_record) {
                        this.nursingRecords.unshift({
                            id: 'hct_' + Date.now(),
                            time: res.data.nursing_record.time,
                            value: res.data.nursing_record.value,
                            nurse: res.data.nursing_record.nurse || this.nurseName || '楚心瑜',
                            deleted: false,
                            deletedMeta: null,
                        });
                    }
                    return true;
                }
                return false;
            } catch (err) {
                console.error("🔴 [Update HCT Error]:", err);
                return false;
            }
        },

        async fetchOrders(checkId) {
            try {
                const targetId = checkId || this.currentPatient?.id;
                if (!targetId || targetId === "—") return [];
                const res = await api.get(`/dialysis-checks/${targetId}/orders`);
                if (res.data?.success) {
                    this.ordersPool = res.data.orders || [];
                    if (this.currentPatient) {
                        this.currentPatient.orderCount = res.data.active_count ?? 0;
                    }
                    for (const g of this.patientGroups) {
                        const pt = g.patients?.find(
                            (p) => p.id === targetId || p.mr === targetId,
                        );
                        if (pt) pt.orderCount = res.data.active_count ?? 0;
                    }
                    return this.ordersPool;
                }
                return [];
            } catch (err) {
                console.error("🔴 [Fetch Orders Error]:", err);
                return [];
            }
        },

        async executeOrder(order, action = "execute", payload = {}) {
            try {
                const orderId = typeof order === "object" ? order.id : order;
                const table =
                    (typeof order === "object" ? order.table : null) ||
                    "doctor_ap_items";
                const checkId = this.currentPatient?.id;

                const res = await api.post(
                    `/orders/${table}/${orderId}/action`,
                    {
                        action: action,
                        complement:
                            payload.complement ||
                            (typeof order === "object"
                                ? order.complement
                                : "") ||
                            "",
                        reason: payload.reason || "",
                        check_id: checkId,
                        nurse_id:
                            sessionStorage.getItem("hismp_user_id") ||
                            localStorage.getItem("hismp_user_id") ||
                            null,
                        nurse_name:
                            sessionStorage.getItem("hismp_user_name") ||
                            localStorage.getItem("hismp_user_name") ||
                            this.nurseName ||
                            "楚心瑜",
                    },
                );

                if (res.data?.success) {
                    const targetOrder = this.ordersPool.find(
                        (o) =>
                            o.id === orderId &&
                            (!order.table || o.table === order.table),
                    );
                    if (targetOrder) {
                        if (action === "execute") {
                            targetOrder.isExecuted = true;
                            targetOrder.isPostponed = false;
                        } else if (action === "postpone") {
                            targetOrder.isExecuted = false;
                            targetOrder.isPostponed = true;
                        } else if (action === "confirm") {
                            targetOrder.isExecuted = true;
                        }
                        targetOrder.execNurse =
                            res.data.exec_nurse || this.nurseName || "楚心瑜";
                        targetOrder.execTime =
                            res.data.exec_time ||
                            new Date().toTimeString().slice(0, 5);
                        if (payload.complement)
                            targetOrder.complement = payload.complement;
                        if (payload.reason) targetOrder.reason = payload.reason;
                    }
                    const activeCount = this.ordersPool.filter(
                        (o) => !o.isExecuted && !o.isPostponed,
                    ).length;
                    if (this.currentPatient) {
                        this.currentPatient.orderCount = activeCount;
                    }
                    for (const g of this.patientGroups) {
                        const pt = g.patients?.find(
                            (p) => p.id === this.currentPatient?.id,
                        );
                        if (pt) pt.orderCount = activeCount;
                    }

                    // 寫入前端本地護理記錄留痕
                    let logVal = "";
                    const contentStr = targetOrder
                        ? targetOrder.content
                        : "醫囑項目";
                    if (action === "execute") {
                        const compStr = payload.complement
                            ? `。護理補充：${payload.complement}`
                            : "";
                        logVal = `[醫囑執行] 完成核對並執行醫師處置：${contentStr}${compStr}`;
                    } else if (action === "postpone") {
                        const reasonStr = payload.reason
                            ? `。延後原因：${payload.reason}`
                            : "";
                        logVal = `[醫囑延後] 醫師處置暫緩執行：${contentStr}${reasonStr}`;
                    } else if (action === "confirm") {
                        logVal = `[處方核對] 護理師核對確認醫師處方變更：${contentStr}`;
                    }

                    if (res.data?.nursing_record) {
                        this.nursingRecords.unshift(res.data.nursing_record);
                    } else if (logVal) {
                        this.nursingRecords.unshift({
                            id: "ord_" + Date.now(),
                            time:
                                res.data.exec_time ||
                                new Date().toTimeString().slice(0, 5),
                            content: logVal,
                            value: logVal,
                            nurse:
                                res.data.exec_nurse ||
                                this.nurseName ||
                                "楚心瑜",
                            deleted: false,
                            deletedMeta: null,
                        });
                    }
                    return true;
                }
                return false;
            } catch (err) {
                console.error("🔴 [Execute Order Error]:", err);
                return false;
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
                return { items: [] };
            }
        },
        async fetchDrawerDetails(checkId, type = null) {
            try {
                const res = await api.get(`/dialysis-checks/${checkId}/drawer-details`);
                const data = res.data;
                if (data && data.success) {
                    if (type === "basicInfo") return data.basic_info;
                    if (type === "orderSheet") return data.order_sheet;
                    if (type === "vascular") return data.vascular;
                    if (type === "anemia") return data.anemia;
                    if (type === "lab") return data.lab;
                    if (type === "longterm") return data.longterm;
                    return data;
                }
                return this.generateMockDetail(this.currentPatient?.mr || "", type);
            } catch (err) {
                console.warn(`⚠️ [fetchDrawerDetails] 透過 check_id ${checkId} 查詢失敗，回退至備用範本:`, err.message);
                return this.generateMockDetail(this.currentPatient?.mr || "", type);
            }
        },
        async fetchPatientDetails(mr, type = null) {
            try {
                const res = await api.get(
                    `/patients/${mr}/dialysis-cases/current`,
                );
                const data = res.data;
                if (data && data.success) {
                    if (type === "basicInfo") return data.basic_info || this.generateMockDetail(mr, "basicInfo");
                    if (type === "orderSheet") return data.order_sheet || this.generateMockDetail(mr, "orderSheet");
                    if (type === "vascular") return data.vascular || this.generateMockDetail(mr, "vascular");
                    if (type === "anemia") return data.anemia || this.generateMockDetail(mr, "anemia");
                    if (type === "lab") return data.lab || this.generateMockDetail(mr, "lab");
                    if (type === "longterm") return data.longterm || this.generateMockDetail(mr, "longterm");
                    return data;
                }
                return this.generateMockDetail(mr, type);
            } catch (err) {
                console.warn("⚠️ [fetchPatientDetails] 回退至標準臨床範本:", err.message);
                return this.generateMockDetail(mr, type);
            }
        },
        generateMockDetail(mr, type) {
            const pt = this.patientGroups
                ?.flatMap((g) => g.patients || [])
                ?.find((p) => p.mr === mr) || this.currentPatient;

            const name = pt?.name || "病患";

            const mockData = {
                basicInfo: {
                    name: name,
                    mr: mr,
                    gender: "女",
                    birthdate: "1958-03-15（66歲）",
                    id_num: "A123****789",
                    bloodType: "A型 Rh+",
                    dialysis_age: "6年2個月",
                    primary_disease: "糖尿病腎病",
                    allergies: ["Penicillin (盤尼西林)"],
                    emergency_contact: "薛*明 0912-345-678",
                    address: "台北市中正區重慶南路一段 100 號",
                    attending_doctor: "張院長",
                },
                orderSheet: {
                    frequency: "3次/週（一三五）",
                    duration: "4h",
                    orders: [
                        { id: 1, name: "Dialyzer", content: "FX80 Classix", note: "每次更換" },
                        { id: 2, name: "BF (血流速)", content: "250 ml/min", note: "" },
                        { id: 3, name: "DF (透析液流速)", content: "500 ml/min", note: "" },
                        { id: 4, name: "Dialysate Ca", content: "3.0 mEq/L", note: "" },
                        { id: 5, name: "Heparin", content: "Initial 2000u / Maintain 300u/hr", note: "" },
                        { id: 6, name: "乾體重", content: (pt?.dry_weight || 59.5) + " kg", note: "上次調整：2026-05-01" },
                    ],
                },
                vascular: {
                    type: "AVF",
                    site: "R't 右臂 (橈動脈-頭靜脈)",
                    created_at: "2020-03-10",
                    last_check_date: "2026-05-27",
                    note: "正常，廔管震顫良好，無狹窄或動脈瘤跡象。",
                    backup_type: "無",
                    backup_note: "AVF 功能健全血流良好，暫無需備用血管通路。",
                },
                anemia: {
                    current_week_idx: 3,
                    weeks: ["4月第3週", "4月第4週", "5月第1週", "5月第2週"],
                    hct_history: [
                        { week: "4月第3週", lw: "29.5", tw: "31.0", value: "31.0" },
                        { week: "4月第4週", lw: "30.0", tw: "32.0", value: "32.0" },
                        { week: "5月第1週", lw: "29.8", tw: "31.5", value: "31.5" },
                        { week: "5月第2週", lw: "30.0", tw: "32.5", value: "32.5" },
                    ],
                    epo_record: "NESP N20（4000IU）｜ W一・W三 施打｜ 最近施打：2026-05-26",
                },
                lab: {
                    last_update: "2026-05-20",
                    labs: [
                        { id: 1, name: "Hb (血紅素)", value: "9.1", range: "11-13 g/dL", date: "2026-05-20", is_abnormal: true, status: "danger" },
                        { id: 2, name: "Ferritin (鐵蛋白)", value: "285", range: "100-500 ng/mL", date: "2026-03-15", is_abnormal: false, status: "normal" },
                        { id: 3, name: "K⁺ (鉀離子)", value: "4.8", range: "3.5-5.0 mEq/L", date: "2026-05-20", is_abnormal: false, status: "normal" },
                        { id: 4, name: "Ca×P (鈣磷乘積)", value: "52", range: "<55 mg²/dL²", date: "2026-05-20", is_abnormal: true, status: "warn" },
                        { id: 5, name: "Kt/V (透析適當率)", value: "1.42", range: ">1.2", date: "2026-05-20", is_abnormal: false, status: "success" },
                    ],
                },
                longterm: {
                    longterm_orders: [
                        { id: 1, name: "Amtrel (降血壓)", dosage: "5mg", frequency: "QD PC", start_date: "2024-01-10" },
                        { id: 2, name: "Medorin (降血壓)", dosage: "#3顆", frequency: "透析前", start_date: "2023-06-15" },
                        { id: 3, name: "Caltrate (碳酸鈣)", dosage: "600mg", frequency: "TID CC", start_date: "2023-08-01" },
                        { id: 4, name: "Rocaltrol (活性維生素D)", dosage: "0.25mcg", frequency: "BIW", start_date: "2024-03-01" },
                    ],
                    dry_weight_history: [
                        { id: 1, date: "2026-05-01", weight: "59.5 kg", reason: "水腫消退，下修 0.5kg", doctor: "張院長" },
                        { id: 2, date: "2026-03-15", weight: "60.0 kg", reason: "透析中血壓偏低，評估無水腫維持", doctor: "張院長" },
                        { id: 3, date: "2026-01-10", weight: "60.5 kg", reason: "年節前體重評估基準設定", doctor: "張院長" },
                    ],
                },
            };

            return type ? mockData[type] : mockData;
        },
        async fetchMonitoringData(check_id) {
            try {
                const res = await api.get(`/dialysis-checks/${check_id}/monitoring`);
                return res.data.data;
            } catch (err) {
                console.error("🔴 [Monitoring Data Error]:", err);
                return [];
            }
        },
        async addNursingRecord(content, time = null, nurse = null) {
            if (!this.currentPatient?.id || this.currentPatient.id === "—" || this.currentPatient.id === "undefined") {
                alert("⚠️ 請先在左側清單選擇病患，再新增護理記錄！");
                console.warn("⚠️ [addNursingRecord]: currentPatient.id 無效，無法發送請求");
                return;
            }
            try {
                // 優先使用 sessionStorage 中的登入者姓名
                const currentNurse =
                    nurse ||
                    sessionStorage.getItem("hismp_user_name") ||
                    this.nurseName ||
                    "護理師";

                // 改用 check_id 進行請求
                const res = await api.post(
                    `/dialysis-checks/${this.currentPatient.id}/nursing-records`,
                    {
                        content: content,
                        time: time,
                        nurse: currentNurse,
                    },
                );
                if (res.data.success) {
                    this.nursingRecords.unshift(res.data.record);
                }
            } catch (err) {
                console.error("🔴 [Nursing Record Error]:", err);
            }
        },
        async executeOnSign(type = "main", action = "sign") {
            if (!this.currentPatient?.id || this.currentPatient.id === "—") return false;
            try {
                const currentNurse =
                    sessionStorage.getItem("hismp_user_name") ||
                    this.nurseName ||
                    "護理師";
                const res = await api.post(`/dialysis-checks/${this.currentPatient.id}/on-sign`, {
                    type: type,
                    action: action,
                    nurse_name: currentNurse,
                });

                if (res.data?.success) {
                    if (res.data.signs) {
                        this.mainSigned = !!res.data.signs.main_signed;
                        this.mainNurseId = res.data.signs.main_nurse_id;
                        this.mainNurseName = res.data.signs.main_nurse_name || "";
                        this.doubleSigned = !!res.data.signs.double_signed;
                        this.doubleNurseId = res.data.signs.double_nurse_id;
                        this.doubleNurseName = res.data.signs.double_nurse_name || "";
                    }
                    if (res.data.nursing_record) {
                        this.nursingRecords.unshift(res.data.nursing_record);
                    }
                    return { success: true };
                }
                return { success: false, message: res.data?.message || "簽章失敗" };
            } catch (err) {
                console.error("🔴 [On-Sign Error]:", err);
                const msg = err.response?.data?.message || "簽章失敗，請稍後重試";
                return { success: false, message: msg };
            }
        },

        async deleteNursingRecord(id) {
            console.log("DEBUG: 準備刪除記錄 ID:", id);
            try {
                // 優先使用 sessionStorage 中的登入者姓名，確保為真正執行註銷的人員
                const realNurseName =
                    sessionStorage.getItem("hismp_user_name") ||
                    this.nurseName ||
                    "護理師";
                const res = await api.delete(`/nursing-records/${id}`, {
                    data: { nurse: realNurseName }
                });
                console.log("DEBUG: 刪除回應:", res.data);
                const idx = this.nursingRecords.findIndex((r) => r.id === id);
                if (idx !== -1) {
                    const nowTime = new Date().toTimeString().slice(0, 5);
                    this.nursingRecords[idx].deleted = true;
                    this.nursingRecords[idx].deletedMeta = res.data?.deletedMeta || `〈${realNurseName} ${nowTime} 註銷〉`;
                }
            } catch (err) {
                console.error("🔴 [Delete Record Error]:", err);
            }
        },
        async fetchNurseWatchings(check_id) {
            this.isNWLoading = true;
            try {
                const res = await api.get(`/dialysis-checks/${check_id}/nurse-watchings`);
                this.nurseWatchings = res.data.records || [];
                return this.nurseWatchings;
            } catch (err) {
                console.error("🔴 [Fetch NW Error]:", err);
                return [];
            } finally {
                this.isNWLoading = false;
            }
        },
        async createNurseWatching(check_id, payload) {
            try {
                let res;
                if (payload instanceof FormData) {
                    res = await api.post(`/dialysis-checks/${check_id}/nurse-watchings`, payload, {
                        headers: { "Content-Type": "multipart/form-data" }
                    });
                } else {
                    res = await api.post(`/dialysis-checks/${check_id}/nurse-watchings`, payload);
                }
                const newRec = res.data.record;
                if (newRec) {
                    this.nurseWatchings.unshift(newRec);
                    if (this.currentPatient) {
                        this.currentPatient.hasNW = true;
                    }
                    for (const g of this.patientGroups) {
                        const pt = g.patients?.find(p => p.id === check_id);
                        if (pt) pt.hasNW = true;
                    }
                    this.nursingRecords.unshift({
                        id: 'nw_' + Date.now(),
                        time: newRec.time_str || new Date().toLocaleTimeString('zh-TW', { hour: '2-digit', minute: '2-digit', hour12: false }),
                        value: '[Nurse Watching 📷] ' + (newRec.content || payload.content || ''),
                        nurse: newRec.nurse_name || this.nurseName || '楚心瑜',
                        deleted: false,
                        deletedMeta: null,
                    });
                }
                return res.data;
            } catch (err) {
                console.error("🔴 [Create NW Error]:", err);
                throw err;
            }
        },
    },
});

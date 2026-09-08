// 血管評估選項
const evalOptions = [
    { label: "正常", val: "0" },
    { label: "紅", val: "1" },
    { label: "腫", val: "2" },
    { label: "熱", val: "3" },
    { label: "痛", val: "4" },
    { label: "滲血", val: "5" },
];

// 意識狀態選項
const consciousOptions = [
    { label: "清醒", val: "0" },
    { label: "嗜睡", val: "1" },
    { label: "木僵", val: "2" },
    { label: "半昏迷", val: "3" },
    { label: "昏迷", val: "4" },
    { label: "失智", val: "5" },
];

// 儲存邏輯 (多選)
const toggleVascularEval = (val) => {
    if (val === "0") {
        assessForm.value.vascularEval = ["0"];
    } else {
        const idx = assessForm.value.vascularEval.indexOf(val);
        const normIdx = assessForm.value.vascularEval.indexOf("0");
        if (normIdx !== -1) assessForm.value.vascularEval.splice(normIdx, 1);
        if (idx === -1) assessForm.value.vascularEval.push(val);
        else assessForm.value.vascularEval.splice(idx, 1);
        if (assessForm.value.vascularEval.length === 0)
            assessForm.value.vascularEval = ["0"];
    }
};

const openAssessModal = async (tabIndex) => {
    try {
        const res = await store.api.get(`/dialysis-checks/${store.currentPatient.id}`);
        const assessData = res.data.assess || {};

        if (Object.keys(assessData).length > 0) {
            // 解析 conscious: "索引,備註"
            const consciousRaw = String(assessData.conscious || "0");
            const [cIdx, ...cNoteParts] = consciousRaw.split(',');
            
            assessForm.value = {
                vascularKind: assessData.vascular_kind || "AVF",
                vascularPosLR: assessData.vascular_pos_lr || "L't",
                vascularPosCenter: assessData.vascular_pos_center || "SCVC",
                permcathLen: assessData.permcath_len || "",
                vascularEval: assessData.vascular_eval ? String(assessData.vascular_eval).split(',') : ["0"],
                vascularNote: assessData.vascular_note || "",
                
                consciousState: consciousOptions.find(o => o.val == cIdx)?.label || "清醒",
                consciousNote: cNoteParts.join(',') || "",
                
                skinState: String(assessData.skin || "0").split(',')[0],
                skinLocation: assessData.skin_location || "",
                skinSize: assessData.skin_size || "",
                skinNote: assessData.skin_note || ""
            };
        } else {
            assessForm.value = {
                vascularKind: "AVF",
                vascularPosLR: "L't",
                vascularPosCenter: "SCVC",
                permcathLen: "",
                vascularEval: ["0"],
                vascularNote: "",
                consciousState: "清醒",
                consciousNote: "",
                skinState: "0",
                skinLocation: "",
                skinSize: "",
                skinNote: "",
                note: ""
            };
        }
        
        // 更新顯示狀態
        store.assessState = {
            vascular: `${assessForm.value.vascularKind} ${assessForm.value.vascularPosLR}${assessForm.value.vascularPosCenter} ${assessForm.value.vascularEval.map(v => evalOptions.find(o => o.val === v)?.label || v).join(',')}`,
            conscious: assessForm.value.consciousState,
            skin: assessForm.value.skinState === '1' ? '有傷口' : '完整'
        };
        
        assessActiveTab.value = tabIndex;
        showAssessModal.value = true;
    } catch (e) { console.error("🔴 [Load Assessment Error]:", e); }
};

const saveUnifiedAssess = async () => {
    try {
        const payload = {
            ...assessForm.value,
            consciousState: consciousOptions.find(o => o.label === assessForm.value.consciousState)?.val || "0",
            skinState: assessForm.value.skinState
        };
        
        await store.api.post(`/dialysis-checks/${store.currentPatient.id}/assessments`, payload);
        
        store.assessState = {
            vascular: `${assessForm.value.vascularKind} ${assessForm.value.vascularPosLR}${assessForm.value.vascularPosCenter} ${assessForm.value.vascularEval.map(v => evalOptions.find(o => o.val === v)?.label || v).join(',')}`,
            conscious: assessForm.value.consciousState,
            skin: assessForm.value.skinState === '1' ? '有傷口' : '完整'
        };
        showAssessModal.value = false;
        alert("✅ 儲存成功");
    } catch (e) { 
        console.error("🔴 [Save Error]:", e);
        alert("⚠️ 儲存失敗"); 
    }
};
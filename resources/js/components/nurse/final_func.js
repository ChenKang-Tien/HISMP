// 意識狀態選項定義
const consciousOptions = [
    { label: '清醒', val: '0' }, { label: '嗜睡', val: '1' }, 
    { label: '木僵', val: '2' }, { label: '半昏迷', val: '3' }, 
    { label: '昏迷', val: '4' }, { label: '失智', val: '5' }
];

// 開啟三大評估
const openAssessModal = async (tabIndex) => {
    try {
        const res = await store.api.get(`/dialysis-checks/${store.currentPatient.id}`);
        const assessData = res.data.assess || {};

        if (Object.keys(assessData).length > 0) {
            assessForm.value = {
                vascularKind: assessData.vascular_kind || "AVF",
                vascularPosLR: assessData.vascular_pos_lr || "L't",
                vascularPosCenter: assessData.vascular_pos_center || "SCVC",
                permcathLen: assessData.permcath_len || "",
                vascularEval: assessData.vascular_eval ? String(assessData.vascular_eval).split(',') : ["0"],
                vascularNote: assessData.vascular_note || "",
                
                consciousState: consciousOptions.find(o => o.val == assessData.conscious)?.label || "清醒",
                consciousNote: assessData.conscious_note || "",
                
                skinState: assessData.skin || "0",
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
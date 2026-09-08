// 開啟三大評估
const openAssessModal = async (tabIndex) => {
    try {
        const res = await store.api.get(`/dialysis-checks/${store.currentPatient.id}`);
        const assessData = res.data.assess || {};

        if (Object.keys(assessData).length > 0) {
            // 解析意識狀態：格式為 "索引,備註"
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
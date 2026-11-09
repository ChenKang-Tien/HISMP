<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-id"></i>🪪 基本資料</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:13.5px;">
                <button class="print-btn-inline" @click="handlePrint">🖨️ 列印</button>
                <div class="info-grid">
                    <div><span class="info-label">姓名：</span>{{ detailData?.name || patient?.name || '—' }}</div>
                    <div><span class="info-label">病歷號：</span>{{ detailData?.mr || patient?.mr || '—' }}</div>
                    <div><span class="info-label">性別：</span>{{ detailData?.gender || '女' }}</div>
                    <div><span class="info-label">生日：</span>{{ detailData?.birthdate || '1958-03-15（66歲）' }}</div>
                    <div><span class="info-label">身分證：</span>{{ detailData?.id_num || 'A123****789' }}</div>
                    <div><span class="info-label">血型：</span>{{ detailData?.bloodType || 'A型 Rh+' }}</div>
                    <div><span class="info-label">透析齡：</span>{{ detailData?.dialysis_age || '6年2個月' }}</div>
                    <div><span class="info-label">原發病：</span>{{ detailData?.primary_disease || '糖尿病腎病' }}</div>
                    <div><span class="info-label">過敏：</span>{{ formatAllergies(detailData?.allergies) }}</div>
                    <div><span class="info-label">緊急聯絡：</span>{{ detailData?.emergency_contact || '薛*明 0912-345-678' }}</div>
                    <div><span class="info-label">地址：</span>{{ detailData?.address || '台北市中正區重慶南路一段 100 號' }}</div>
                    <div><span class="info-label">主治醫師：</span>{{ detailData?.attending_doctor || '張院長' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps(['modelValue', 'patient', 'detailData']);
defineEmits(['update:modelValue']);

const formatAllergies = (allg) => {
    if (!allg) return '無';
    if (Array.isArray(allg)) return allg.length > 0 ? allg.join(', ') : '無';
    return String(allg);
};

const handlePrint = () => {
    window.print();
};
</script>

<style scoped>
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex !important; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-hdr { background: #0f766e; color: white; padding: 10px 14px; font-size: 13px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; margin: -16px -16px 16px -16px; border-radius: 13px 13px 0 0; }
.modal-x { background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
.print-btn-inline { font-size: 11px; background: #e2e8f0; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; margin-bottom: 10px; font-weight: 600; color: #334155; }
.print-btn-inline:hover { background: #cbd5e1; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; line-height: 1.6; }
.info-label { color: #64748b; font-weight: 600; }
</style>

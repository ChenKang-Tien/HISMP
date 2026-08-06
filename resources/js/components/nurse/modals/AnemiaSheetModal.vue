<template>
    <div v-if="modelValue" class="modal-overlay" :class="{ open: modelValue }" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-droplet"></i>🩸 貧血治療</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            <div class="modal-body" style="font-size:12px;">
                <button class="print-btn-inline" style="margin-bottom:10px;">🖨️ 列印</button>
                <div style="font-weight:700;color:#334155;margin-bottom:6px;">HCT 歷史記錄（最多六週）</div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <button style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:2px 8px;cursor:pointer;font-size:12px;">◀</button>
                    <span style="font-size:11px;font-weight:700;color:#0f766e;">5月第2週</span>
                    <button style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:2px 8px;cursor:pointer;font-size:12px;">▶</button>
                </div>
                <div style="display:flex;gap:6px;margin-bottom:10px;">
                    <div v-for="val in (detailData?.hct_history || [])" :key="val.week" style="flex:1;background:#f0fdfa;border:1.5px solid #99f6e4;border-radius:7px;padding:8px;text-align:center;">
                        <div style="font-size:10px;color:#64748b;">{{ val.week }}</div>
                        <div style="font-size:16px;font-weight:700;color:#0f766e;">{{ val.value }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps(['modelValue', 'patient', 'detailData']);
defineEmits(['update:modelValue']);
</script>

<style scoped>
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex !important; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-hdr { background: #0f766e; color: white; padding: 10px 14px; font-size: 13px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; margin: -16px -16px 16px -16px; border-radius: 13px 13px 0 0; }
.modal-x { background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
.print-btn-inline { font-size: 11px; background: #e2e8f0; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer; }
</style>

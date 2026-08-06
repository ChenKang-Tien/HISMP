<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-id"></i>🪪 基本資料</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:12px;">
                <button class="print-btn-inline">🖨️ 列印</button>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px;">
                    <div><span style="color:var(--slate-lt)">姓名：</span>{{ detailData?.name || patient?.name }}</div>
                    <div><span style="color:var(--slate-lt)">病歷號：</span>{{ patient?.mr }}</div>
                    <div><span style="color:var(--slate-lt)">性別：</span>{{ detailData?.gender || '—' }}</div>
                    <div><span style="color:var(--slate-lt)">生日：</span>{{ detailData?.birthdate || '—' }}</div>
                    <div><span style="color:var(--slate-lt)">血型：</span>{{ detailData?.bloodType || '—' }}</div>
                    <div><span style="color:var(--slate-lt)">過敏：</span>{{ detailData?.allergies?.join(', ') || '無' }}</div>
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

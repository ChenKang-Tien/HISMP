<template>
    <div v-if="modelValue" class="modal-overlay" :class="{ open: modelValue }" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-test-pipe"></i>🧪 檢驗記錄</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:12px;">
                <button class="print-btn-inline" style="margin-bottom:10px;">🖨️ 列印</button>
                <div style="font-size:11px;color:#64748b;margin-bottom:8px">
                    {{ detailData?.last_update ? `最近一次：${detailData.last_update}` : '暫無檢驗記錄' }}
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                  <tr style="background:#0f766e;color:white;">
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">項目</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">數值</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">正常範圍</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">日期</th>
                  </tr>
                  <tr v-for="item in (detailData?.labs || [])" :key="item.id">
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ item.name }}</td>
                    <td style="padding:4px 8px;border:1px solid #e2e8f0" :style="{ color: item.is_abnormal ? '#dc2626' : 'inherit', fontWeight: item.is_abnormal ? '700' : 'normal' }">
                        {{ item.value }}
                    </td>
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ item.range }}</td>
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ item.date }}</td>
                  </tr>
                  <tr v-if="!detailData?.labs || detailData.labs.length === 0">
                    <td colspan="4" style="padding:8px;text-align:center;color:#64748b">無檢驗記錄</td>
                  </tr>
                </table>
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

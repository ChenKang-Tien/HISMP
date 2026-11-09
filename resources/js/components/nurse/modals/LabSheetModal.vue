<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-test-pipe"></i>🧪 檢驗記錄</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:13.5px;">
                <button class="print-btn-inline" style="margin-bottom:10px;" @click="handlePrint">🖨️ 列印</button>
                <div style="font-size:13px;color:#475569;margin-bottom:10px;font-weight:600;">
                    最近一次：{{ detailData?.last_update || '2026-05-20' }}
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                  <thead>
                    <tr style="background:#0f766e;color:white;">
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">項目</th>
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">數值</th>
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">正常範圍</th>
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">日期</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in labsList" :key="item.id">
                      <td style="padding:8px 10px;border:1px solid #e2e8f0;font-weight:600;">{{ item.name }}</td>
                      <td style="padding:8px 10px;border:1px solid #e2e8f0" :style="getValueStyle(item)">
                          {{ item.value }}
                      </td>
                      <td style="padding:8px 10px;border:1px solid #e2e8f0;color:#64748b">{{ item.range }}</td>
                      <td style="padding:8px 10px;border:1px solid #e2e8f0;color:#64748b">{{ item.date }}</td>
                    </tr>
                  </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps(['modelValue', 'patient', 'detailData']);
defineEmits(['update:modelValue']);

const defaultLabs = [
    { id: 1, name: 'Hb (血紅素)', value: '9.1', range: '11-13 g/dL', date: '2026-05-20', is_abnormal: true, status: 'danger' },
    { id: 2, name: 'Ferritin (鐵蛋白)', value: '285', range: '100-500 ng/mL', date: '2026-03-15', is_abnormal: false, status: 'normal' },
    { id: 3, name: 'K⁺ (鉀離子)', value: '4.8', range: '3.5-5.0 mEq/L', date: '2026-05-20', is_abnormal: false, status: 'normal' },
    { id: 4, name: 'Ca×P (鈣磷乘積)', value: '52', range: '<55 mg²/dL²', date: '2026-05-20', is_abnormal: true, status: 'warn' },
    { id: 5, name: 'Kt/V (透析適當率)', value: '1.42', range: '>1.2', date: '2026-05-20', is_abnormal: false, status: 'success' },
];

const labsList = computed(() => {
    if (props.detailData?.labs && props.detailData.labs.length > 0) {
        return props.detailData.labs;
    }
    return defaultLabs;
});

const getValueStyle = (item) => {
    if (item.status === 'danger' || (item.is_abnormal && item.status !== 'warn')) {
        return { color: '#dc2626', fontWeight: '700' };
    }
    if (item.status === 'warn') {
        return { color: '#d97706', fontWeight: '700' };
    }
    if (item.status === 'success') {
        return { color: '#0f766e', fontWeight: '700' };
    }
    return { color: '#1e293b', fontWeight: 'normal' };
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
.print-btn-inline { font-size: 11px; background: #e2e8f0; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-weight: 600; color: #334155; }
.print-btn-inline:hover { background: #cbd5e1; }
</style>

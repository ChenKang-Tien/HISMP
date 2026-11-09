<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-clipboard-list"></i>📝 醫囑單</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:13.5px;">
                <button class="print-btn-inline" style="margin-bottom:10px;" @click="handlePrint">🖨️ 列印</button>
                <div style="font-size:13px;color:#475569;margin-bottom:10px;font-weight:600;">
                    {{ detailData?.frequency || '透析頻率：3次/週（一三五）' }} ｜ {{ detailData?.duration || '透析時數：4h' }}
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                  <thead>
                    <tr style="background:#0f766e;color:white;">
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">項目</th>
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">內容</th>
                      <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">備注</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in ordersList" :key="item.id">
                      <td style="padding:8px 10px;border:1px solid #e2e8f0;font-weight:600;">{{ item.name }}</td>
                      <td style="padding:8px 10px;border:1px solid #e2e8f0">{{ item.content }}</td>
                      <td style="padding:8px 10px;border:1px solid #e2e8f0;color:#64748b">{{ item.note || '—' }}</td>
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

const defaultOrders = [
    { id: 1, name: 'Dialyzer (人工腎臟)', content: 'FX80 Classix', note: '每次更換' },
    { id: 2, name: 'BF (血流速)', content: '250 ml/min', note: '' },
    { id: 3, name: 'DF (透析液流速)', content: '500 ml/min', note: '' },
    { id: 4, name: 'Dialysate Ca (透析液鈣)', content: '3.0 mEq/L', note: '' },
    { id: 5, name: 'Heparin (抗凝劑)', content: 'Initial 2000u / Maintain 300u/hr', note: '' },
    { id: 6, name: '乾體重', content: '59.5 kg', note: '上次調整：2026-05-01' },
];

const ordersList = computed(() => {
    if (props.detailData?.orders && props.detailData.orders.length > 0) {
        return props.detailData.orders;
    }
    return defaultOrders;
});

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

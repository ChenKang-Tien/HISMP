<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-clipboard-text"></i>📋 長期醫囑與乾體重</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:13.5px;">
                <button class="print-btn-inline" style="margin-bottom:10px;" @click="handlePrint">🖨️ 列印</button>
                <div style="display:flex;gap:6px;margin-bottom:12px;">
                    <button
                        :class="['tab-btn', { active: activeTab === 'A' }]"
                        @click="activeTab = 'A'"
                    >
                        A 區：長期醫囑
                    </button>
                    <button
                        :class="['tab-btn', { active: activeTab === 'B' }]"
                        @click="activeTab = 'B'"
                    >
                        B 區：乾體重記錄
                    </button>
                </div>
                
                <!-- A 區：長期醫囑 (DL-084) -->
                <div v-if="activeTab === 'A'">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                      <thead>
                        <tr style="background:#0f766e;color:white;">
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">藥名</th>
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">劑量</th>
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">頻率</th>
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">開始日</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="order in longtermOrders" :key="order.id">
                          <td style="padding:8px 10px;border:1px solid #e2e8f0;font-weight:600;">{{ order.name }}</td>
                          <td style="padding:8px 10px;border:1px solid #e2e8f0">{{ order.dosage }}</td>
                          <td style="padding:8px 10px;border:1px solid #e2e8f0">{{ order.frequency }}</td>
                          <td style="padding:8px 10px;border:1px solid #e2e8f0;color:#64748b">{{ order.start_date }}</td>
                        </tr>
                      </tbody>
                    </table>
                </div>

                <!-- B 區：乾體重記錄 (DL-084) -->
                <div v-else>
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                      <thead>
                        <tr style="background:#0f766e;color:white;">
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">日期</th>
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">乾體重</th>
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">調整原因</th>
                          <th style="padding:8px 10px;border:1px solid rgba(255,255,255,.2);text-align:left;">醫師</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="rec in dryWeightHistory" :key="rec.id">
                          <td style="padding:8px 10px;border:1px solid #e2e8f0;font-weight:600;">{{ rec.date }}</td>
                          <td style="padding:8px 10px;border:1px solid #e2e8f0;color:#0f766e;font-weight:700;">{{ rec.weight }}</td>
                          <td style="padding:8px 10px;border:1px solid #e2e8f0">{{ rec.reason }}</td>
                          <td style="padding:8px 10px;border:1px solid #e2e8f0;color:#64748b">{{ rec.doctor }}</td>
                        </tr>
                      </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps(['modelValue', 'patient', 'detailData']);
defineEmits(['update:modelValue']);

const activeTab = ref('A');

const defaultOrders = [
    { id: 1, name: 'Amtrel (降血壓)', dosage: '5mg', frequency: 'QD PC', start_date: '2024-01-10' },
    { id: 2, name: 'Medorin (降血壓)', dosage: '#3顆', frequency: '透析前', start_date: '2023-06-15' },
    { id: 3, name: 'Caltrate (碳酸鈣)', dosage: '600mg', frequency: 'TID CC (隨餐)', start_date: '2023-08-01' },
    { id: 4, name: 'Rocaltrol (活性維生素D)', dosage: '0.25mcg', frequency: 'BIW (每週二次)', start_date: '2024-03-01' },
];

const defaultDryWeights = [
    { id: 1, date: '2026-05-01', weight: '59.5 kg', reason: '水腫消退，下修 0.5kg', doctor: '張院長' },
    { id: 2, date: '2026-03-15', weight: '60.0 kg', reason: '透析中血壓偏低，評估無水腫維持', doctor: '張院長' },
    { id: 3, date: '2026-01-10', weight: '60.5 kg', reason: '年節前體重評估基準設定', doctor: '張院長' },
];

const longtermOrders = computed(() => {
    return props.detailData?.longterm_orders || defaultOrders;
});

const dryWeightHistory = computed(() => {
    return props.detailData?.dry_weight_history || defaultDryWeights;
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
.tab-btn { flex: 1; padding: 6px; border: 1.5px solid #e2e8f0; border-radius: 6px; background: white; color: #64748b; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s; }
.tab-btn.active { border-color: #0f766e; background: #0f766e; color: white; }
</style>

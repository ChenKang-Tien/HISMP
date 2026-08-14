<template>
    <div v-if="modelValue" class="modal-overlay" :class="{ open: modelValue }" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-clipboard-text"></i>📋 長期醫囑與乾體重</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:12px;">
                <button class="print-btn-inline" style="margin-bottom:10px;">🖨️ 列印</button>
                <div style="display:flex;gap:6px;margin-bottom:10px;">
                    <button style="flex:1;padding:6px;border:2px solid #0f766e;border-radius:6px;background:#0f766e;color:white;font-size:11px;font-weight:700;cursor:pointer;">A 區：長期醫囑</button>
                    <button style="flex:1;padding:6px;border:2px solid #e2e8f0;border-radius:6px;background:white;color:#64748b;font-size:11px;font-weight:700;cursor:pointer;">B 區：乾體重記錄</button>
                </div>
                
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                  <tr style="background:#0f766e;color:white;">
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">藥名</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">劑量</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">頻率</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">開始日</th>
                  </tr>
                  <tr v-for="order in (detailData?.longterm_orders || [])" :key="order.id">
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ order.name }}</td>
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ order.dosage }}</td>
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ order.frequency }}</td>
                    <td style="padding:4px 8px;border:1px solid #e2e8f0">{{ order.start_date }}</td>
                  </tr>
                  <tr v-if="!detailData?.longterm_orders || detailData.longterm_orders.length === 0">
                    <td colspan="4" style="padding:8px;text-align:center;color:#64748b">無長期醫囑記錄</td>
                  </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useDialysisStore } from "@/store/useNurseStore";
defineProps(['modelValue', 'patient', 'detailData']);
defineEmits(['update:modelValue']);
const store = useDialysisStore();
</script>

<style scoped>
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex !important; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-hdr { background: #0f766e; color: white; padding: 10px 14px; font-size: 13px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; margin: -16px -16px 16px -16px; border-radius: 13px 13px 0 0; }
.modal-x { background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
.print-btn-inline { font-size: 11px; background: #e2e8f0; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer; }
</style>

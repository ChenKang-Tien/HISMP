<template>
    <div v-if="modelValue" class="modal-overlay" :class="{ open: modelValue }" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-clipboard-list"></i>📝 醫囑單</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:12px;">
                <button class="print-btn-inline" style="margin-bottom:10px;">🖨️ 列印</button>
                <div style="font-size:11px;color:var(--slate-lt);margin-bottom:8px">透析頻率：3次/週（一三五）｜ 透析時數：4h</div>
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                  <tr style="background:#0f766e;color:white;">
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">項目</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">內容</th>
                    <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">備注</th>
                  </tr>
                  <tr><td style="padding:4px 8px;border:1px solid #e2e8f0">Dialyzer</td><td style="padding:4px 8px;border:1px solid #e2e8f0">FX80 Classix</td><td style="padding:4px 8px;border:1px solid #e2e8f0">每次更換</td></tr>
                  <tr><td style="padding:4px 8px;border:1px solid #e2e8f0">BF</td><td style="padding:4px 8px;border:1px solid #e2e8f0">250 ml/min</td><td style="padding:4px 8px;border:1px solid #e2e8f0"></td></tr>
                  <tr><td style="padding:4px 8px;border:1px solid #e2e8f0">DF</td><td style="padding:4px 8px;border:1px solid #e2e8f0">500 ml/min</td><td style="padding:4px 8px;border:1px solid #e2e8f0"></td></tr>
                  <tr><td style="padding:4px 8px;border:1px solid #e2e8f0">Dialysate Ca</td><td style="padding:4px 8px;border:1px solid #e2e8f0">3.0 mEq/L</td><td style="padding:4px 8px;border:1px solid #e2e8f0"></td></tr>
                  <tr><td style="padding:4px 8px;border:1px solid #e2e8f0">Heparin</td><td style="padding:4px 8px;border:1px solid #e2e8f0">Initial 2000u / Maintain 300u/hr</td><td style="padding:4px 8px;border:1px solid #e2e8f0"></td></tr>
                  <tr><td style="padding:4px 8px;border:1px solid #e2e8f0">乾體重</td><td style="padding:4px 8px;border:1px solid #e2e8f0">59.5 kg</td><td style="padding:4px 8px;border:1px solid #e2e8f0">上次調整：2026-05-01</td></tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps(['modelValue']);
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

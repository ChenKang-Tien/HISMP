<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal" style="max-width:600px">
    <div class="modal-hdr"><i class="ti ti-clipboard-list"></i>📝 醫囑單</div>
    <button class="print-btn-inline" >🖨️ 列印</button>
    <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
    <div style="font-size:11px;color:var(--slate-lt);margin-bottom:8px">透析頻率：3次/週（一三五）｜ 透析時數：4h</div>
    <table style="width:100%;border-collapse:collapse;font-size:11px;">
      <tr style="background:var(--teal-dk);color:white;">
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">項目</th>
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">內容</th>
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">備注</th>
      </tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Dialyzer</td><td style="padding:4px 8px;border:1px solid var(--border)">FX80 Classix</td><td style="padding:4px 8px;border:1px solid var(--border)">每次更換</td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">BF</td><td style="padding:4px 8px;border:1px solid var(--border)">250 ml/min</td><td style="padding:4px 8px;border:1px solid var(--border)"></td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">DF</td><td style="padding:4px 8px;border:1px solid var(--border)">500 ml/min</td><td style="padding:4px 8px;border:1px solid var(--border)"></td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Dialysate Ca</td><td style="padding:4px 8px;border:1px solid var(--border)">3.0 mEq/L</td><td style="padding:4px 8px;border:1px solid var(--border)"></td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Heparin</td><td style="padding:4px 8px;border:1px solid var(--border)">Initial 2000u / Maintain 300u/hr</td><td style="padding:4px 8px;border:1px solid var(--border)"></td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">乾體重</td><td style="padding:4px 8px;border:1px solid var(--border)">59.5 kg</td><td style="padding:4px 8px;border:1px solid var(--border)">上次調整：2026-05-01</td></tr>
    </table>
    </div>
</template>

<script setup>
defineProps(['modelValue']);
defineEmits(['update:modelValue']);
</script>

<style scoped>
.modal-overlay { display: flex; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 500; align-items: center; justify-content: center; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-x { position: absolute; top: 12px; right: 12px; background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
</style>

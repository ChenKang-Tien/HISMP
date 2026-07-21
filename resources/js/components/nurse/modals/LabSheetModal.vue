<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal" style="max-width:600px">
    <div class="modal-hdr"><i class="ti ti-test-pipe"></i>🧪 檢驗記錄</div>
    <button class="print-btn-inline" >🖨️ 列印</button>
    <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
    <div style="font-size:11px;color:var(--slate-lt);margin-bottom:8px">最近一次：2026-05-20</div>
    <table style="width:100%;border-collapse:collapse;font-size:11px;">
      <tr style="background:var(--teal-dk);color:white;">
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">項目</th>
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">數值</th>
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">正常範圍</th>
        <th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">日期</th>
      </tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Hb</td><td style="padding:4px 8px;border:1px solid var(--border);color:var(--red);font-weight:700">9.1</td><td style="padding:4px 8px;border:1px solid var(--border)">11-13 g/dL</td><td style="padding:4px 8px;border:1px solid var(--border)">2026-05-20</td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Ferritin</td><td style="padding:4px 8px;border:1px solid var(--border)">285</td><td style="padding:4px 8px;border:1px solid var(--border)">100-500 ng/mL</td><td style="padding:4px 8px;border:1px solid var(--border)">2026-03-15</td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">K⁺</td><td style="padding:4px 8px;border:1px solid var(--border)">4.8</td><td style="padding:4px 8px;border:1px solid var(--border)">3.5-5.0 mEq/L</td><td style="padding:4px 8px;border:1px solid var(--border)">2026-05-20</td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Ca×P</td><td style="padding:4px 8px;border:1px solid var(--border);color:var(--amber);font-weight:700">52</td><td style="padding:4px 8px;border:1px solid var(--border)">&lt;55 mg²/dL²</td><td style="padding:4px 8px;border:1px solid var(--border)">2026-05-20</td></tr>
      <tr><td style="padding:4px 8px;border:1px solid var(--border)">Kt/V</td><td style="padding:4px 8px;border:1px solid var(--border);color:var(--green);font-weight:700">1.42</td><td style="padding:4px 8px;border:1px solid var(--border)">&gt;1.2</td><td style="padding:4px 8px;border:1px solid var(--border)">2026-05-20</td></tr>
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

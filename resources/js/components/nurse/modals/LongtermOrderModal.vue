<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal" style="max-width:600px">
    <div class="modal-hdr"><i class="ti ti-clipboard-text"></i>📋 長期醫囑與乾體重</div>
    <button class="print-btn-inline" >🖨️ 列印</button>
    <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
    <div style="display:flex;gap:6px;margin-bottom:10px;">
      <button  style="flex:1;padding:6px;border:2px solid var(--teal);border-radius:6px;background:var(--teal);color:white;font-size:11px;font-weight:700;cursor:pointer;">A 區：長期醫囑</button>
      <button  style="flex:1;padding:6px;border:2px solid var(--border);border-radius:6px;background:white;color:var(--slate);font-size:11px;font-weight:700;cursor:pointer;">B 區：乾體重記錄</button>
    </div>
    <div id="lt-A">
      <table style="width:100%;border-collapse:collapse;font-size:11px;">
        <tr style="background:var(--teal-dk);color:white;"><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">藥名</th><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">劑量</th><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">頻率</th><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">開始日</th></tr>
        <tr><td style="padding:4px 8px;border:1px solid var(--border)">Amtrel</td><td style="padding:4px 8px;border:1px solid var(--border)">5mg</td><td style="padding:4px 8px;border:1px solid var(--border)">QD</td><td style="padding:4px 8px;border:1px solid var(--border)">2024-01-10</td></tr>
        <tr><td style="padding:4px 8px;border:1px solid var(--border)">Medorin</td><td style="padding:4px 8px;border:1px solid var(--border)">#3顆</td><td style="padding:4px 8px;border:1px solid var(--border)">透析前</td><td style="padding:4px 8px;border:1px solid var(--border)">2023-06-15</td></tr>
      </table>
    </div>
    <div id="lt-B" style="display:none;">
      <table style="width:100%;border-collapse:collapse;font-size:11px;">
        <tr style="background:var(--teal-dk);color:white;"><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">日期</th><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">乾體重</th><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">調整原因</th><th style="padding:4px 8px;border:1px solid rgba(255,255,255,.2)">醫師</th></tr>
        <tr><td style="padding:4px 8px;border:1px solid var(--border)">2026-05-01</td><td style="padding:4px 8px;border:1px solid var(--border)">59.5 kg</td><td style="padding:4px 8px;border:1px solid var(--border)">水腫改善</td><td style="padding:4px 8px;border:1px solid var(--border)">張院醫師</td></tr>
        <tr><td style="padding:4px 8px;border:1px solid var(--border)">2026-03-15</td><td style="padding:4px 8px;border:1px solid var(--border)">60.0 kg</td><td style="padding:4px 8px;border:1px solid var(--border)">體重穩定</td><td style="padding:4px 8px;border:1px solid var(--border)">張院醫師</td></tr>
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

<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal" style="max-width:620px">
    <div class="modal-hdr"><i class="ti ti-droplet"></i>🩸 貧血治療</div>
    <button class="print-btn-inline" >🖨️ 列印</button>
    <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
    <div style="font-size:11px;font-weight:700;color:var(--slate);margin-bottom:6px;">HCT 歷史記錄（最多六週）</div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
      <button  style="background:var(--slate-ul);border:1px solid var(--border);border-radius:4px;padding:2px 8px;cursor:pointer;font-size:12px;">◀</button>
      <span style="font-size:11px;font-weight:700;color:var(--teal);" id="hct-week-label">5月第2週</span>
      <button  style="background:var(--slate-ul);border:1px solid var(--border);border-radius:4px;padding:2px 8px;cursor:pointer;font-size:12px;">▶</button>
    </div>
    <div style="display:flex;gap:6px;margin-bottom:10px;" id="hct-week-cols">
      <div style="flex:1;background:var(--teal-lt);border:1.5px solid var(--teal-bd);border-radius:7px;padding:8px;text-align:center;">
        <div style="font-size:10px;color:var(--slate-lt);">LW</div>
        <div style="font-size:16px;font-weight:700;color:var(--teal);">30.0%
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

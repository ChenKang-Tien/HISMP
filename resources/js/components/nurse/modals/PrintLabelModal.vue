<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
    <div class="modal-hdr"><i class="ti ti-printer"></i>📄 列印病患聯絡溝通標籤</div>
    <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
    <div style="font-size:11px;color:var(--slate-lt);margin-bottom:10px">預設全選。點擊項目查看細節並細選要列印的內容。</div>
    <div class="print-item checked" ><div class="chk-box">✓</div><span style="flex:1;">病患姓名 ／ 透析日期</span><span style="font-size:10px;color:var(--teal)">▸ 預覽</span></div>
    <div class="print-item checked" ><div class="chk-box">✓</div><span style="flex:1;">透析前／後數據：體重、血壓、脈搏</span><span style="font-size:10px;color:var(--teal)">▸ 查看</span></div>
    <div id="print-vitals-detail" style="display:none;background:var(--slate-ul);border-radius:5px;padding:6px 10px;margin-bottom:4px;font-size:10px;color:var(--slate);">
      透前：78.5 kg ／ 148/90 ／ PR 72 ・ 透後：75.0 kg ／ 138/84 ／ PR 74
    </div>
    <div class="print-item checked" ><div class="chk-box">✓</div><span style="flex:1;">水分控制：兩次透析間體重增加量</span><span style="font-size:10px;color:var(--teal)">▸ 預覽</span></div>
    <div class="print-item checked" ><div class="chk-box">✓</div><span style="flex:1;">用藥紀錄：磷結合劑、降血壓藥、EPO 施打與服用</span><span style="font-size:10px;color:var(--teal)">▸ 預覽</span></div>
    <div class="print-item checked" ><div class="chk-box">✓</div><span style="flex:1;">檢驗報告與身體不適反應</span><span style="font-size:10px;color:var(--teal)">▸ 預覽</span></div>
    <div class="print-item checked" ><div class="chk-box">✓</div><span style="flex:1;">護理記錄</span><span style="font-size:10px;color:var(--teal)">▸ 細選</span></div>
    <div id="print-nursing-detail" style="display:none;background:var(--slate-ul);border-radius:5px;padding:6px 10px;margin-bottom:4px;">
      <div style="font-size:10px;color:var(--slate);margin-bottom:5px;">勾選要列印的記錄：</div>
      <div class="chk-row checked"  style="font-size:10px;padding:4px 6px;"><div class="chk-box" style="width:14px;height:14px;font-size:9px;">✓</div>@09:20 低血壓 BP 92/54，快衝 N/S 100ml，UFR 調降。楚心瑜</div>
      <div class="chk-row checked"  style="font-size:10px;padding:4px 6px;"><div class="chk-box" style="width:14px;height:14px;font-size:9px;">✓</div>@08:05 透析上針順利，廔管穿刺無滲血，意識清楚。楚心瑜</div>
    </div>
  </div>
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

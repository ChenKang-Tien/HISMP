<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-activity-heartbeat"></i>🌿 血管通路</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:12px;">
                <button class="print-btn-inline" style="margin-bottom:10px;">🖨️ 列印</button>
                <div style="display:flex;gap:6px;margin-bottom:10px;">
                    <button style="flex:1;padding:6px;border:2px solid #0f766e;border-radius:6px;background:#0f766e;color:white;font-size:11px;font-weight:700;cursor:pointer;">A 區</button>
                    <button style="flex:1;padding:6px;border:2px solid #e2e8f0;border-radius:6px;background:white;color:#64748b;font-size:11px;font-weight:700;cursor:pointer;">B 區</button>
                </div>
                <div id="vascular-A">
                    <div style="font-size:11px;"><strong>種類：</strong>AVF｜<strong>位置：</strong>R't 右臂｜<strong>建立日期：</strong>2020-03-10</div>
                    <div style="font-size:11px;margin-top:6px;"><strong>最近評估（2026-05-27）：</strong>正常，廔管震顫良好</div>
                </div>
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

<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr"><i class="ti ti-package"></i>明日庫房領料大總表</div>
            <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            <div style="font-size:11px;color:var(--slate-lt);margin-bottom:8px">系統自動精算，點擊數量可查看配給病患名單</div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--slate-ul);border-radius:7px;margin-bottom:4px;cursor:pointer" class="supply-item-row" data-item="FX80 Classix 人工腎臟" id="supply-row-FX80 C" >
                <span style="display:flex;align-items:center;gap:7px;font-size:12px"><i class="ti ti-droplet" style="color:var(--teal)"></i>FX80 Classix 人工腎臟</span>
                <span class="supply-count-badge" id="badge-FX80" style="background:var(--teal);color:white;font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px">12 組 ℹ️</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--slate-ul);border-radius:7px;margin-bottom:4px;cursor:pointer" class="supply-item-row" data-item="Heparin 1000u/ml" id="supply-row-Hepari" >
                <span style="display:flex;align-items:center;gap:7px;font-size:12px"><i class="ti ti-droplet" style="color:var(--purple)"></i>Heparin 1000u/ml</span>
                <span class="supply-count-badge" id="badge-Hep" style="background:var(--teal);color:white;font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px">24 支 ℹ️</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--slate-ul);border-radius:7px;margin-bottom:4px;cursor:pointer" class="supply-item-row" data-item="EPO 4000IU（Epogin）" id="supply-row-EPO 40" >
                <span style="display:flex;align-items:center;gap:7px;font-size:12px"><i class="ti ti-syringe" style="color:var(--amber)"></i>EPO 4000IU（Epogin）</span>
                <span class="supply-count-badge" id="badge-EPO" style="background:var(--teal);color:white;font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px">8 支 ℹ️</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--slate-ul);border-radius:7px;margin-bottom:4px;cursor:pointer" class="supply-item-row" data-item="IRON（Ferinject）100mg" id="supply-row-IRON（F" >
                <span style="display:flex;align-items:center;gap:7px;font-size:12px"><i class="ti ti-pill" style="color:var(--red)"></i>IRON（Ferinject）100mg</span>
                <span class="supply-count-badge" id="badge-IRON" style="background:var(--teal);color:white;font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px">3 支 ℹ️</span>
            </div>
            <div style="background:var(--amber-lt);border:1px solid var(--amber-bd);border-radius:6px;padding:6px 10px;font-size:11px;color:#92400e;margin-top:8px">⚠️ 鎖定後若醫師修改明日醫囑，隔日白班登入時系統將跳出黃色告警</div>
            <div class="mbtn-row">
                <button class="mbtn sec" @click="$emit('update:modelValue', false)">關閉</button>
                <button class="mbtn pri" style="background:var(--green); opacity:.5; cursor:not-allowed;" id="lock-supply-btn" disabled>🔒 具名鎖定領料清單</button>
                <div id="lock-supply-hint" style="font-size:10px;color:var(--amber);margin-top:4px;display:none;">⚠️ 請先完成所有品項確認</div>
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

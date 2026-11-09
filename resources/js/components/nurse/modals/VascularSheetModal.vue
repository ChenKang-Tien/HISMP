<template>
    <div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
            <div class="modal-hdr">
                <span><i class="ti ti-activity-heartbeat"></i>🌿 血管通路</span>
                <button class="modal-x" @click="$emit('update:modelValue', false)">✕</button>
            </div>
            
            <div class="modal-body" style="font-size:13.5px;">
                <button class="print-btn-inline" style="margin-bottom:10px;" @click="handlePrint">🖨️ 列印</button>
                <div style="display:flex;gap:6px;margin-bottom:12px;">
                    <button
                        :class="['tab-btn', { active: activeTab === 'A' }]"
                        @click="activeTab = 'A'"
                    >
                        A 區 (主要通路)
                    </button>
                    <button
                        :class="['tab-btn', { active: activeTab === 'B' }]"
                        @click="activeTab = 'B'"
                    >
                        B 區 (備用通路)
                    </button>
                </div>

                <div v-if="activeTab === 'A'" class="vascular-tab-content">
                    <div style="font-size:13.5px;line-height:2;">
                        <strong>種類：</strong>{{ vascularData.type }} ｜
                        <strong>位置：</strong>{{ vascularData.site }} ｜
                        <strong>建立日期：</strong>{{ vascularData.created_at }}
                    </div>
                    <div style="font-size:13.5px;margin-top:10px;padding:10px 14px;background:#f0fdfa;border:1px solid #99f6e4;border-radius:6px;">
                        <strong style="color:#0f766e;">最近評估（{{ vascularData.last_check_date }}）：</strong>
                        <span style="color:#1e293b;">{{ vascularData.note }}</span>
                    </div>
                </div>

                <div v-else class="vascular-tab-content">
                    <div style="font-size:13.5px;line-height:2;">
                        <strong>備用通路：</strong>{{ vascularData.backup_type || '無' }} ｜
                        <strong>狀態：</strong>{{ vascularData.backup_status || 'AVF 功能良好，無需備用' }}
                    </div>
                    <div style="font-size:13px;margin-top:10px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;color:#64748b;line-height:1.7;">
                        {{ vascularData.backup_note || '備註：主要血管通路震顫明顯，穿刺條件優良，目前未建立永久或臨時備用雙腔導管。' }}
                    </div>
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

const defaultVascular = {
    type: 'AVF (自體動靜脈廔管)',
    site: "R't 右臂 (橈動脈-頭靜脈)",
    created_at: '2020-03-10',
    last_check_date: '2026-05-27',
    note: '正常，廔管震顫良好，無狹窄或動脈瘤跡象。',
    backup_type: '無',
    backup_status: 'AVF 功能良好，無需備用',
    backup_note: '主要血管通路震顫明顯，穿刺條件優良，目前未建立永久或臨時備用雙腔導管。'
};

const vascularData = computed(() => {
    return {
        ...defaultVascular,
        ...(props.detailData || {})
    };
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
.vascular-tab-content { padding: 4px 0; }
</style>

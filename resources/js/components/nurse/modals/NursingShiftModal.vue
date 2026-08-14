<template>
    <BaseModal v-model="internalValue" title="護理排班設定">
        <div class="shift-setup-container">
            <div class="form-group">
                <label>日期：</label>
                <input type="date" v-model="formData.date" class="form-input">
            </div>
            <div class="form-group">
                <label>選擇護理師：</label>
                <select v-model="formData.nurse_id" class="form-input">
                    <option value="">請選擇護理師</option>
                    <option v-for="n in options.nurses" :key="n.id" :value="n.id">{{ n.name }}</option>
                </select>
            </div>
            <div class="form-group">
                <label>指定群組 (Group)：</label>
                <select v-model="formData.group_id" class="form-input">
                    <option value="">請選擇群組</option>
                    <option v-for="g in options.groups" :key="g.id" :value="g.id">{{ g.name }}</option>
                </select>
            </div>
            <div class="group-preview" v-if="formData.group_id">
                <label>該群組下病患清單 (自動關聯)：</label>
                <div class="patient-list-mini">
                    <!-- 顯示該組別的所有病患 -->
                </div>
            </div>
            <div class="mbtn-row">
                <button class="mbtn sec" @click="internalValue = false">取消</button>
                <button class="mbtn pri" @click="submit">確認排班</button>
            </div>
        </div>
    </BaseModal>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import BaseModal from './BaseModal.vue';
import { useDialysisStore } from "@/store/useNurseStore";

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'confirm']);
const store = useDialysisStore();

const internalValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const formData = reactive({
    date: new Date().toISOString().split('T')[0],
    nurse_id: '',
    group_id: '',
    note: ''
});

const options = ref({ nurses: [], groups: [] });

onMounted(async () => {
    options.value = await store.fetchShiftOptions();
});

const submit = async () => {
    const success = await store.saveShift(formData);
    if (success) {
        internalValue.value = false;
    }
};
</script>

<style scoped>
.shift-setup-container { padding: 16px; }
.form-group { margin-bottom: 15px; }
.form-input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
.patient-list-mini { background: #f9fafb; padding: 10px; border-radius: 4px; font-size: 13px; }
</style>

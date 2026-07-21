<template>
<div v-if="modelValue" class="modal-overlay open" @click.self="$emit('update:modelValue', false)">
        <div class="modal">
    <div class="modal-hdr"><i class="ti ti-package"></i>明日庫房領料大總表</div>
    </div></div>
</template>

<script setup>
defineProps(['modelValue']);
defineEmits(['update:modelValue']);
</script>

<style scoped>
.modal-overlay { display: flex; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 500; align-items: center; justify-content: center; }
.modal { background: white; border-radius: 13px; padding: 16px; width: 90%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.3); max-height: 88vh; overflow-y: auto; position: relative; }
.modal-x { position: absolute; top: 12px; right: 12px; background: #b91c1c; border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; }
</style>

<template>
    <WeightManagementModal
        :model-value="modelValue"
        :initial-category="initialCategory"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>

<script setup>
import WeightManagementModal from "./WeightManagementModal.vue";

defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    initialCategory: {
        type: String,
        default: "pre",
    },
});

defineEmits(["update:modelValue"]);
</script>

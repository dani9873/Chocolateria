<script setup>
defineProps({
    for: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <label :for="for" class="block font-medium text-sm text-gray-700">
        {{ value }}
    </label>
</template>
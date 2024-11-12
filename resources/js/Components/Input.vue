<script setup>
defineProps({
    modelValue: [String, Number],
    type: {
        type: String,
        default: 'text',
    },
    id: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <input :id="id" :type="type" :value="modelValue" :placeholder="placeholder" :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
        class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
</template>
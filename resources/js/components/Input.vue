<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string | number | null | undefined;
    type?: string;
    id?: string;
    placeholder?: string;
    required?: boolean;
    min?: number | string;
    max?: number | string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    class: 'block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
}>();

const value = computed({
    get: () => props.modelValue ?? '',
    set: (val) => {
        if (props.type === 'number') {
            const num = val === '' ? null : parseFloat(String(val));
            emit('update:modelValue', isNaN(num as number) ? null : num);
        } else {
            emit('update:modelValue', val);
        }
    },
});
</script>

<template>
    <input
        :type="props.type"
        :id="props.id"
        :placeholder="props.placeholder"
        :required="props.required"
        :min="props.min"
        :max="props.max"
        v-model="value"
        :class="props.class"
    />
</template>

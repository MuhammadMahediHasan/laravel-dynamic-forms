<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: any;
    id?: string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    class: 'block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
    (e: 'change', event: Event): void;
}>();

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

function handleChange(event: Event) {
    emit('change', event);
}
</script>

<template>
    <select
        :id="props.id"
        v-model="value"
        @change="handleChange"
        :class="props.class"
    >
        <slot />
    </select>
</template>

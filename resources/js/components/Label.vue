<script setup lang="ts">
interface Props {
    for?: string;
    required?: boolean;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    class: 'block text-xs font-semibold text-gray-700 dark:text-gray-300',
});
</script>

<template>
    <label :for="props.for" :class="props.class">
        <slot />
        <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
</template>

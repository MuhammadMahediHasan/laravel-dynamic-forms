<script setup lang="ts">
import SvgIcon from './SvgIcon.vue';
import Draggable from 'vuedraggable';
import { FORM_BUILDER_GROUP } from '../constants/formBuilder';
import type { AvailableElement, FormElement } from '../types/formBuilder';

defineProps<{
    availableElements: AvailableElement[];
    isLoading: boolean;
    isProcessing: boolean;
    canSubmit: boolean;
    createElement: (element: AvailableElement) => FormElement;
}>();

const emit = defineEmits<{
    submit: [];
    dragStart: [event: { item?: HTMLElement & { _underlying_vm_?: { type?: string } } }];
    dragEnd: [];
}>();
</script>

<template>
    <div
        class="sticky top-4 flex max-h-[calc(100vh-120px)] flex-col overflow-hidden rounded-lg border border-border/80 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
    >
        <div
            class="border-b border-border/80 bg-muted/30 px-4 py-3 dark:bg-gray-900/40"
        >
            <h3 class="text-sm font-semibold text-foreground">
                Field Library
            </h3>
            <p class="mt-0.5 text-xs text-muted-foreground">
                Drag a field into the builder
            </p>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <div v-if="isLoading" class="space-y-2">
                <div
                    v-for="index in 6"
                    :key="index"
                    class="h-12 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg mb-2"
                ></div>
            </div>

            <Draggable
                v-else
                :list="availableElements"
                :group="{
                    name: FORM_BUILDER_GROUP,
                    pull: 'clone',
                    put: false,
                }"
                :sort="false"
                :clone="createElement"
                item-key="type"
                class="space-y-2"
                @start="emit('dragStart', $event)"
                @end="emit('dragEnd')"
            >
                <template #item="{ element }">
                    <div
                        class="flex cursor-grab items-center gap-3 rounded-lg border border-border/70 bg-background px-3 py-2.5 transition-colors hover:border-primary/30 hover:bg-primary/5 active:cursor-grabbing"
                    >
                        <span
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary/10"
                        >
                            <SvgIcon
                                :name="element.icon"
                                class="h-4 w-4 text-primary"
                            />
                        </span>
                        <span
                            class="pointer-events-none text-sm font-medium text-foreground"
                        >
                            {{ element.label || element.name }}
                        </span>
                    </div>
                </template>
            </Draggable>
        </div>

        <div
            class="rounded-b-lg border-t border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
        >
            <button
                type="button"
                :disabled="!canSubmit || isProcessing"
                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors disabled:opacity-50 cursor-pointer"
                @click="emit('submit')"
            >
                <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submit
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import SvgIcon from './SvgIcon.vue';
import Draggable from 'vuedraggable';
import { FORM_BUILDER_GROUP } from '../constants/formBuilder';
import type { FormElement } from '../types/formBuilder';
import FormElementComponent from './FormElement.vue';
import FormGroupChildren from './FormGroupChildren.vue';
import Preview from './Preview.vue';
import { getConfigLocales } from '../utils/formOptions';

const formElements = defineModel<FormElement[]>('formElements', {
    required: true,
});

const resolvedLocales = getConfigLocales();

defineProps<{
    previewMode: boolean;
    previewLang: string;
    previewFormValues: Record<string, unknown>;
    isLoading: boolean;
    allFieldsCollapsed: boolean;
    totalElementsCount: number;
    formErrors: Record<string, string>;
    allFormElements: FormElement[];
    isGradableType: boolean;
    hoveringGroupId: string | null;
    getElementIndex: (elementId: string) => number;
    getFlatElementIndex: (elementId: string) => number;
    canMoveIntoGroup: (event: {
        draggedContext?: { element?: { type?: string } };
        dragged?: HTMLElement & { _underlying_vm_?: { type?: string } };
    }) => boolean;
    canPutInGroup: (
        to: unknown,
        from: unknown,
        dragEl: HTMLElement & { _underlying_vm_?: { type?: string } },
    ) => boolean;
}>();

const emit = defineEmits<{
    'update:previewLang': [string];
    togglePreviewMode: [];
    toggleAllFieldsCollapsed: [];
    dragStart: [event: { item?: HTMLElement & { _underlying_vm_?: { type?: string } } }];
    dragEnd: [];
    updateElement: [id: string, field: string, value: unknown];
    removeElement: [id: string];
    groupDragEnter: [groupId: string, event: DragEvent];
    groupDragLeave: [groupId: string, event: DragEvent];
    builderDragStart: [type: string];
    builderDragEnd: [];
    groupChildrenChange: [groupId: string];
    submitPreview: [];
}>();
</script>

<template>
    <div
        class="flex flex-col overflow-y-auto rounded-lg bg-white shadow-sm dark:bg-gray-800"
    >
        <div
            class="sticky top-0 z-10 flex items-center justify-between border-b border-primary/10 bg-primary/5 px-6 py-3 dark:border-primary/20 dark:bg-primary/10"
        >
            <div class="flex items-center gap-3">
                <h3
                    class="text-lg font-semibold text-[var(--primary-dark)] dark:text-primary"
                >
                    {{ previewMode ? 'Form Preview' : 'Form Builder' }}
                </h3>
                <span
                    v-if="!previewMode && totalElementsCount > 0"
                    class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                >
                    {{ totalElementsCount }} element{{
                        totalElementsCount === 1 ? '' : 's'
                    }}
                </span>
            </div>

            <div v-if="!isLoading" class="flex items-center space-x-3">
                <div
                    v-if="previewMode"
                    class="inline-flex overflow-hidden rounded-md border border-primary/20"
                >
                    <button
                        v-for="loc in resolvedLocales"
                        :key="loc"
                        type="button"
                        class="px-2 py-1 text-xs uppercase"
                        :class="
                            previewLang === loc
                                ? 'bg-primary text-white'
                                : 'bg-transparent text-muted-foreground hover:text-primary'
                        "
                        @click="emit('update:previewLang', loc)"
                    >
                        {{ loc }}
                    </button>
                </div>

                <button
                    v-if="!previewMode && formElements.length > 0"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md border border-primary/20 px-2.5 py-1 text-xs font-medium transition-colors"
                    :class="
                        allFieldsCollapsed
                            ? 'bg-primary text-white'
                            : 'bg-transparent text-primary hover:bg-primary/10'
                    "
                    @click="emit('toggleAllFieldsCollapsed')"
                >
                    <SvgIcon
                        :name="
                            allFieldsCollapsed
                                ? 'mdi:unfold-more-horizontal'
                                : 'mdi:unfold-less-horizontal'
                        "
                        class="h-4 w-4"
                    />
                    {{ allFieldsCollapsed ? 'Expand all' : 'Collapse all' }}
                </button>

                <SvgIcon
                    class="h-6 w-6 cursor-pointer rounded-full bg-primary/10 p-0.5 text-primary hover:bg-primary/15 hover:text-primary/80"
                    :name="previewMode ? 'mdi:eye-off' : 'mdi:eye'"
                    @click="emit('togglePreviewMode')"
                />
            </div>
        </div>

        <div
            class="min-h-96 flex-1 overflow-y-auto bg-muted/15 p-4 dark:bg-gray-900/30 sm:p-5"
        >
            <div v-if="isLoading" class="space-y-4">
                <div class="h-12 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
                <div class="h-12 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
                <div class="h-12 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
            </div>

            <div
                v-else
                :class="{
                    'mx-auto w-full max-w-4xl': previewMode,
                }"
            >
                <template v-if="previewMode">
                    <Preview
                        v-for="(element, index) in formElements"
                        :key="element.id"
                        :element="element"
                        :index="index"
                        :lang="previewLang"
                        :form-values="previewFormValues"
                    />
                </template>

                <Draggable
                    v-else
                    v-model="formElements"
                    item-key="id"
                    class="space-y-3"
                    :animation="200"
                    ghost-class="ghost"
                    :group="{
                        name: FORM_BUILDER_GROUP,
                        pull: true,
                        put: true,
                    }"
                    @start="emit('dragStart', $event)"
                    @end="emit('dragEnd')"
                >
                    <template #header>
                        <div
                            v-if="formElements.length === 0"
                            class="flex min-h-[18rem] flex-col items-center justify-center rounded-xl border-2 border-dashed border-primary/25 bg-background/70 px-6 py-10 text-center dark:bg-gray-800/50"
                        >
                            <div
                                class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10"
                            >
                                <SvgIcon
                                    name="mdi:cursor-move"
                                    class="h-7 w-7 text-primary"
                                />
                            </div>
                            <p class="mb-2 text-lg font-semibold text-foreground">
                                Start building your form
                            </p>
                            <p class="mb-5 max-w-sm text-sm text-muted-foreground">
                                Drag elements from the left panel and drop them
                                here in any order
                            </p>
                        </div>
                    </template>

                    <template #item="{ element }">
                        <div
                            :data-form-element-id="element.id"
                            :class="
                                element.type === 'group'
                                    ? 'overflow-hidden rounded-lg border border-border bg-card shadow-sm transition-shadow hover:border-primary/20 hover:shadow-md'
                                    : ''
                            "
                        >
                            <FormElementComponent
                                :class="
                                    element.type === 'group'
                                        ? '!rounded-none !border-0 !shadow-none hover:!border-0 hover:!shadow-none'
                                        : ''
                                "
                                :element="element"
                                :index="getElementIndex(element.id)"
                                :errors="formErrors"
                                :all-form-elements="allFormElements"
                                :is-gradable-type="isGradableType"
                                :collapse-all="allFieldsCollapsed"
                                :can-move-up="getElementIndex(element.id) > 0"
                                :can-move-down="
                                    getElementIndex(element.id) <
                                    formElements.length - 1
                                "
                                @update="
                                    (id, field, value) =>
                                        emit('updateElement', id, field, value)
                                "
                                @remove="emit('removeElement', $event)"
                            />

                            <FormGroupChildren
                                v-if="element.type === 'group'"
                                attached
                                :group="element"
                                :collapse-all="allFieldsCollapsed"
                                :form-errors="formErrors"
                                :all-form-elements="allFormElements"
                                :is-gradable-type="isGradableType"
                                :hovering-group-id="hoveringGroupId"
                                :get-flat-element-index="getFlatElementIndex"
                                :can-move-into-group="canMoveIntoGroup"
                                :can-put-in-group="canPutInGroup"
                                @update="
                                    (id, field, value) =>
                                        emit('updateElement', id, field, value)
                                "
                                @remove="emit('removeElement', $event)"
                                @group-drag-enter="
                                    (groupId, event) =>
                                        emit('groupDragEnter', groupId, event)
                                "
                                @group-drag-leave="
                                    (groupId, event) =>
                                        emit('groupDragLeave', groupId, event)
                                "
                                @builder-drag-start="
                                    (type) => emit('builderDragStart', type)
                                "
                                @builder-drag-end="emit('builderDragEnd')"
                                @group-children-change="
                                    (groupId) =>
                                        emit('groupChildrenChange', groupId)
                                "
                            />
                        </div>
                    </template>
                </Draggable>

                <button
                    v-if="previewMode && formElements.length > 0"
                    type="button"
                    class="mt-3 w-full inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors cursor-pointer"
                    @click="emit('submitPreview')"
                >
                    Submit
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ghost {
    opacity: 0.4;
    background-color: color-mix(in srgb, var(--primary) 12%, transparent);
}
</style>

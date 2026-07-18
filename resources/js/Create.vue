<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, useAttrs } from 'vue';
import FormBuilderFieldLibrary from './components/FormBuilderFieldLibrary.vue';
import FormBuilderSettingsForm from './components/FormBuilderSettingsForm.vue';
import FormBuilderWorkspace from './components/FormBuilderWorkspace.vue';
import SvgIcon from './components/SvgIcon.vue';
import { useFormBuilderDragDrop } from './composables/useFormBuilderDragDrop';
import { useFormBuilderElements } from './composables/useFormBuilderElements';
import { fetchAvailableElements } from './composables/useFormBuilderSubmit';
import { DEFAULT_STATUS } from './constants/formBuilder';
import type {
    AvailableElement,
    FormBuilderProps,
    FormElement,
    TypeData,
} from './types/formBuilder';
import { initializeFormElements, flattenElementsForSubmit } from './utils/formElementTree';
import { getConfigLocales } from './utils/formOptions';

const props = defineProps<FormBuilderProps>();

const page = usePage();
const attrs = useAttrs();
const emit = defineEmits<{
    (e: 'submit', payload: { form: any; elements: any[] }): void;
    (e: 'cancel'): void;
    (e: 'error', message: string): void;
}>();

const backToUrl = () => {
    emit('cancel');
};

const isLoading = ref(false);
const previewMode = ref(false);
const previewLang = ref<string>(getConfigLocales()[0] || 'en');
const allFieldsCollapsed = ref(true);
const formElements = ref<FormElement[]>(
    initializeFormElements(props.editData?.elements),
);
const availableElements = ref<AvailableElement[]>(
    props.availableElements || [],
);
const types = ref<TypeData[]>(props.types || []);
const previewFormValues = ref<Record<string, unknown>>({});

const form = useForm({
    form_id: props.editData?.form_id || '',
    type:
        props.editData?.type ||
        props.presetType ||
        '',
    name: props.editData?.name || '',
    status: props.editData?.status || DEFAULT_STATUS,
    elements: [] as FormElement[],
    quiz_title: props.editData?.quiz_title || null,
    quiz_description: props.editData?.quiz_description || null,
    passing_score: props.editData?.passing_score ?? 70,
    max_attempts: props.editData?.max_attempts ?? null,
    time_limit_minutes: props.editData?.time_limit_minutes ?? null,
    shuffle_questions: props.editData?.shuffle_questions ?? false,
    shuffle_options: props.editData?.shuffle_options ?? false,
    show_result_immediately: props.editData?.show_result_immediately ?? true,
    quiz_is_active: props.editData?.quiz_is_active ?? true,
    description: props.editData?.description ?? null,
    end_at: props.editData?.end_at
        ? new Date(props.editData.end_at as string | Date)
        : null,
});

const {
    createElement,
    getElementIndex,
    updateElement,
    removeElement,
    moveElementUp,
    moveElementDown,
    removeGroupFromChildren,
    allFormElementsFlattened,
    totalElementsCount,
    getFlatElementIndex,
} = useFormBuilderElements(formElements);

const {
    hoveringGroupId,
    canMoveIntoGroup,
    canPutInGroup,
    handleBuilderDragStart,
    handleBuilderDragStartByType,
    handleBuilderDragEnd,
    handleGroupDragEnter,
    handleGroupDragLeave,
    removeGroupFromChildren: removeNestedGroupsFromChildren,
} = useFormBuilderDragDrop({
    removeGroupFromChildren,
});

const isProcessing = computed(() => props.isProcessing ?? false);

const onSubmit = () => {
    if (formElements.value.length === 0 && !props.editData?.form_id) {
        emit('error', 'Please add at least one form element before submitting.');
        return;
    }
    emit('submit', {
        form,
        elements: flattenElementsForSubmit(formElements.value),
    });
};

const isGradableType = computed(
    () => form.type === 'Quiz',
);

const canSubmitForm = computed(
    () => formElements.value.length > 0 || !!props.editData?.form_id,
);

const togglePreviewMode = () => {
    previewMode.value = !previewMode.value;
};

const toggleAllFieldsCollapsed = () => {
    allFieldsCollapsed.value = !allFieldsCollapsed.value;
};

const submitPreviewForm = () => {
    console.error('Preview Form Data:', formElements.value);
};

const loadAvailableElements = async () => {
    isLoading.value = true;

    try {
        availableElements.value = await fetchAvailableElements();
    } catch (error) {
        emit('error', 'Failed to fetch available elements');
        console.error('Failed to fetch available elements:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    if (!props.availableElements?.length) {
        await loadAvailableElements();
    }
});
</script>

<template>
    <div class="ldf-container space-y-6">
        <div class="ldf-header flex items-center justify-between">
            <h2
                class="ldf-title text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200"
            >
                Dynamic Form
            </h2>

            <button
                type="button"
                class="ldf-back-btn inline-flex items-center gap-1.5 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                @click="backToUrl()"
            >
                <SvgIcon name="mdi:arrow-left" class="ldf-back-btn-icon h-3.5 w-3.5 text-gray-500" />
                Go Back
            </button>
        </div>

        <div class="ldf-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 md:p-6 shadow-sm">
            <div class="ldf-content text-gray-900 dark:text-gray-100">
                <form class="ldf-form" @submit.prevent="onSubmit">
                    <FormBuilderSettingsForm
                        :form="form"
                        :types="types"
                        :is-loading="isLoading"
                        :is-editing="!!editData?.form_id"
                    />
                </form>

                <section class="ldf-workspace-section mt-5">
                    <div class="min-h-screen">
                        <div class="mx-auto w-full">
                            <div class="ldf-layout-grid grid grid-cols-1 gap-6 lg:grid-cols-4">
                                <div
                                    v-if="!previewMode"
                                    class="ldf-library-col lg:col-span-1"
                                >
                                    <FormBuilderFieldLibrary
                                        :available-elements="availableElements"
                                        :is-loading="isLoading"
                                        :is-processing="isProcessing"
                                        :can-submit="canSubmitForm"
                                        :create-element="createElement"
                                        @submit="onSubmit"
                                        @drag-start="handleBuilderDragStart"
                                        @drag-end="handleBuilderDragEnd"
                                    />
                                </div>

                                <div
                                    class="ldf-workspace-col"
                                    :class="
                                        previewMode
                                            ? 'lg:col-span-4'
                                            : 'lg:col-span-3'
                                    "
                                >
                                    <FormBuilderWorkspace
                                        v-model:form-elements="formElements"
                                        :preview-mode="previewMode"
                                        :preview-lang="previewLang"
                                        :preview-form-values="previewFormValues"
                                        :is-loading="isLoading"
                                        :all-fields-collapsed="allFieldsCollapsed"
                                        :total-elements-count="totalElementsCount"
                                        :form-errors="form.errors"
                                        :all-form-elements="allFormElementsFlattened"
                                        :is-gradable-type="isGradableType"
                                        :hovering-group-id="hoveringGroupId"
                                        :get-element-index="getElementIndex"
                                        :get-flat-element-index="getFlatElementIndex"
                                        :can-move-into-group="canMoveIntoGroup"
                                        :can-put-in-group="canPutInGroup"
                                        @update:preview-lang="previewLang = $event"
                                        @toggle-preview-mode="togglePreviewMode"
                                        @toggle-all-fields-collapsed="toggleAllFieldsCollapsed"
                                        @drag-start="handleBuilderDragStart"
                                        @drag-end="handleBuilderDragEnd"
                                        @update-element="updateElement"
                                        @remove-element="removeElement"
                                        @move-up="moveElementUp"
                                        @move-down="moveElementDown"
                                        @group-drag-enter="handleGroupDragEnter"
                                        @group-drag-leave="handleGroupDragLeave"
                                        @builder-drag-start="handleBuilderDragStartByType"
                                        @builder-drag-end="handleBuilderDragEnd"
                                        @group-children-change="removeNestedGroupsFromChildren"
                                        @submit-preview="submitPreviewForm"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>


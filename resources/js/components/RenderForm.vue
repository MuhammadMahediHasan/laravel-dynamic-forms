<script setup lang="ts">
import { ref, computed, onMounted, inject, watch } from 'vue';
import { initializeFormElements } from '../utils/formElementTree';
import Label from './Label.vue';
import Input from './Input.vue';
import Select from './Select.vue';

interface Props {
    slug: string;
    apiPrefix?: string;
    lang?: string;
    respondentId?: string | number;
    respondentType?: string;
    subjectId?: string | number;
    subjectType?: string;
}

const props = withDefaults(defineProps<Props>(), {
    apiPrefix: '/api/v1/df',
    lang: 'en',
});

const emit = defineEmits<{
    (e: 'success', data: any): void;
    (e: 'error', errors: any): void;
    (e: 'load-success', form: any): void;
}>();

const customComponents = inject('df-components', {
    Label,
    Input,
    Select,
}) as Record<string, any>;

const formDetails = ref<any>(null);
const formElements = ref<any[]>([]);
const formValues = ref<Record<string | number, any>>({});
const isLoading = ref(true);
const isSubmitting = ref(false);
const serverErrors = ref<Record<string | number, string>>({});
const generalError = ref<string>('');

// Fetch form structure
const fetchForm = async () => {
    isLoading.value = true;
    generalError.value = '';
    serverErrors.value = {};
    try {
        const response = await fetch(`${props.apiPrefix}/${props.slug}`);
        if (!response.ok) {
            throw new Error(`Failed to fetch form: ${response.statusText}`);
        }
        const res = await response.json();
        formDetails.value = res.data;
        formElements.value = initializeFormElements(res.data.elements);
        
        // Initialize values
        const values: Record<string | number, any> = {};
        const initValues = (items: any[]) => {
            items.forEach(item => {
                if (item.type === 'checkbox' || item.type === 'multiSelect') {
                    values[item.id] = [];
                } else if (item.type === 'group') {
                    if (item.children) {
                        initValues(item.children);
                    }
                } else {
                    values[item.id] = '';
                }
            });
        };
        initValues(formElements.value);
        formValues.value = values;
        emit('load-success', res.data);
    } catch (e: any) {
        generalError.value = e.message || 'Failed to load form.';
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.slug, fetchForm);

onMounted(fetchForm);

// Submit form
const submittedItems = computed(() => {
    const items: any[] = [];
    const collectItems = (elements: any[]) => {
        elements.forEach(element => {
            if (element.type === 'group') {
                if (element.children) {
                    collectItems(element.children);
                }
            } else if (element.type !== 'header') {
                items.push({
                    dynamicFormInputId: element.id,
                    value: formValues.value[element.id],
                });
            }
        });
    };
    collectItems(formElements.value);
    return items;
});

const submitForm = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    serverErrors.value = {};
    generalError.value = '';

    const payload = {
        respondent_id: props.respondentId || null,
        respondent_type: props.respondentType || null,
        subject_id: props.subjectId || null,
        subject_type: props.subjectType || null,
        items: submittedItems.value,
    };

    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken;
    }

    try {
        const response = await fetch(`${props.apiPrefix}/${props.slug}/submissions`, {
            method: 'POST',
            headers,
            body: JSON.stringify(payload),
        });

        const res = await response.json();

        if (response.status === 422) {
            const errorsMap: Record<string | number, string> = {};
            if (res.errors) {
                Object.entries(res.errors).forEach(([key, msg]: any) => {
                    const match = key.match(/^items\.(\d+)\.(value|dynamicFormInputId)$/);
                    if (match) {
                        const index = parseInt(match[1], 10);
                        const item = submittedItems.value[index];
                        if (item) {
                            errorsMap[item.dynamicFormInputId] = msg;
                        }
                    } else {
                        generalError.value = msg;
                    }
                });
            }
            serverErrors.value = errorsMap;
            emit('error', res.errors);
        } else if (!response.ok) {
            throw new Error(res.message || 'Submission failed.');
        } else {
            emit('success', res.data);
            // Reset form values
            Object.keys(formValues.value).forEach(key => {
                if (Array.isArray(formValues.value[key])) {
                    formValues.value[key] = [];
                } else {
                    formValues.value[key] = '';
                }
            });
        }
    } catch (e: any) {
        generalError.value = e.message || 'An error occurred during submission.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="ldf-renderer">
        <div v-if="isLoading" class="flex justify-center items-center py-10">
            <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div v-else-if="generalError && !formDetails" class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">
            {{ generalError }}
        </div>

        <form v-else @submit.prevent="submitForm" class="space-y-6">
            <div v-if="generalError" class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-sm">
                {{ generalError }}
            </div>

            <div v-for="element in formElements" :key="element.id" class="space-y-1">
                <!-- Header -->
                <div v-if="element.type === 'header'" class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-2">
                        {{ element.label?.[lang] || element.label?.en }}
                    </h3>
                </div>

                <!-- Group -->
                <div v-else-if="element.type === 'group'" class="mb-4 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-gray-50/50 dark:bg-gray-900/30">
                    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 font-semibold text-sm text-gray-700 dark:text-gray-300">
                        {{ element.label?.[lang] || element.label?.en || 'Group' }}
                    </div>
                    <div class="p-4 space-y-4">
                        <div v-for="child in (element.children || [])" :key="child.id" class="space-y-1">
                            <component :is="customComponents.Label" :for="child.id" :required="child.required">
                                {{ child.label?.[lang] || child.label?.en }}
                            </component>

                            <!-- Select Field inside Group -->
                            <component 
                                :is="customComponents.Select" 
                                v-if="child.type === 'select'"
                                :id="child.id"
                                v-model="formValues[child.id]"
                                :options="(child.options?.[lang] || child.options?.en || []).map((opt: any) => ({ value: opt.value, label: opt.label }))"
                            />

                            <!-- Standard inputs inside Group -->
                            <component 
                                :is="customComponents.Input" 
                                v-else
                                :id="child.id"
                                :type="child.type"
                                v-model="formValues[child.id]"
                                :placeholder="child.placeholder?.[lang] || child.placeholder?.en"
                            />

                            <span v-if="serverErrors[child.id]" class="text-xs text-red-500 block">
                                {{ serverErrors[child.id] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Standalone Input / Select -->
                <template v-else>
                    <component :is="customComponents.Label" :for="element.id" :required="element.required">
                        {{ element.label?.[lang] || element.label?.en }}
                    </component>

                    <!-- Select Field -->
                    <component 
                        :is="customComponents.Select" 
                        v-if="element.type === 'select'"
                        :id="element.id"
                        v-model="formValues[element.id]"
                        :options="(element.options?.[lang] || element.options?.en || []).map((opt: any) => ({ value: opt.value, label: opt.label }))"
                    />

                    <!-- Radio -->
                    <div v-else-if="element.type === 'radio'" class="flex gap-4 items-center flex-wrap pt-1">
                        <label v-for="(opt, idx) in (element.options?.[lang] || element.options?.en || [])" :key="idx" class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input 
                                type="radio" 
                                :name="element.id" 
                                :value="opt.value" 
                                v-model="formValues[element.id]"
                                class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            {{ opt.label }}
                        </label>
                    </div>

                    <!-- Checkbox -->
                    <div v-else-if="element.type === 'checkbox'" class="flex gap-4 items-center flex-wrap pt-1">
                        <label v-for="(opt, idx) in (element.options?.[lang] || element.options?.en || [])" :key="idx" class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input 
                                type="checkbox" 
                                :value="opt.value" 
                                v-model="formValues[element.id]"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            {{ opt.label }}
                        </label>
                    </div>

                    <!-- Regular Inputs -->
                    <component 
                        :is="customComponents.Input" 
                        v-else
                        :id="element.id"
                        :type="element.type"
                        v-model="formValues[element.id]"
                        :placeholder="element.placeholder?.[lang] || element.placeholder?.en"
                    />

                    <span v-if="serverErrors[element.id]" class="text-xs text-red-500 block">
                        {{ serverErrors[element.id] }}
                    </span>
                </template>
            </div>

            <!-- Submit Button slot or default -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <slot name="submit" :isSubmitting="isSubmitting">
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white rounded-xl font-semibold shadow-md shadow-indigo-500/10 transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <span v-if="isSubmitting">Submitting...</span>
                        <span v-else>Submit</span>
                    </button>
                </slot>
            </div>
        </form>
    </div>
</template>

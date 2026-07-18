<script setup lang="ts">
import { computed, ref } from "vue";
import SvgIcon from './SvgIcon.vue';
import { parseOptionItems, getConfigLocales, type FormElementOptions } from '../utils/formOptions';

function isImageUrl(val?: string): boolean {
    if (!val) return false;
    try {
        const u = new URL(val);
        return /\.(png|jpg|jpeg|gif|svg|webp)$/i.test(u.pathname);
    } catch {
        return false;
    }
}

// Types & Interfaces
interface LocalizedText extends Record<string, string> {}

interface FormElement {
    id: string;
    type: string;
    label: LocalizedText;
    placeholder: LocalizedText;
    hints?: LocalizedText;
    icon?: string;
    children?: FormElement[];
    required: boolean;
    has_action: boolean;
    options: FormElementOptions | LocalizedText;
    condition_input_id?: string | number | null;
    condition_value?: string;
    is_repeatable?: boolean;
    repeat_min?: number;
    repeat_max?: number | null;
}

interface Props {
    element: FormElement;
    index: number;
    lang?: string;
    formValues?: Record<string, any>;
}

interface SelectOption {
    value: string;
    label: string;
}

interface InputAttributes {
    placeholder?: string;
    required?: boolean;
    class?: string;
    type?: string;
    rows?: number;
    optionLabel?: string;
    optionValue?: string;
    options?: SelectOption[];
}

const props = defineProps<Props>();

const resolvedLocales = getConfigLocales();

// Local state for language toggle (used only if parent doesn't pass lang)
const activeLang = ref<string>(props.lang ?? (resolvedLocales[0] || 'en'));
const currentLang = computed<string>(() => (props.lang ?? activeLang.value));

// Use provided formValues or create local one
const localFormValues = ref<Record<string, any>>({});
const formValues = computed(() => props.formValues ?? localFormValues.value);

// Track repeat counts locally (not shared with parent)
const repeatCounts = ref<Record<string, number>>({});

// Get form value from shared state
const getFormValue = (elementId: string | number | null) => {
    if (!elementId) return null;
    return formValues.value[elementId];
};

// Update form value and emit to parent if needed
const updateFormValue = (elementId: string, value: any) => {
    if (props.formValues) {
        // If formValues came from parent, update the parent's state
        formValues.value[elementId] = value;
    } else {
        // Otherwise update local state
        localFormValues.value[elementId] = value;
    }
};

// Check if this element should be displayed based on condition
const shouldDisplay = computed(() => {
    if (!props.element.condition_input_id) {
        return true; // No condition, always display
    }
    const conditionValue = getFormValue(props.element.condition_input_id);
    return conditionValue === props.element.condition_value;
});

// Computed Properties
const inputProps = computed<InputAttributes>(() => {
    const type = props.element.type;
    const attrs: InputAttributes = {
        placeholder: props.element.placeholder?.[currentLang.value] ?? '',
        required: props.element.required,
        class: "w-full",
    };

    if (type === 'select' || type === 'multiSelect' || type === 'radio' || type === 'checkbox') {
        const localeOptions = props.element.options?.[currentLang.value];

        return {
            ...attrs,
            optionLabel: 'label',
            optionValue: 'value',
            options: parseOptionItems(localeOptions).map(
                (opt): SelectOption => ({
                    value: opt.value,
                    label: opt.label,
                }),
            ),
        };
    }

    if (type === 'date') {
        // Remove 'type' property for date inputs
        const { type: _type, ...dateAttrs } = attrs;
        return dateAttrs;
    }

    if (type === 'file') {
        // Placeholder isn't applicable to file input
        const { placeholder: _ph, ...rest } = attrs;
        return rest;
    }

    if (type === 'textarea') {
        return {
            ...attrs,
            rows: 4,
        };
    }

    return {
        ...attrs,
        type,
    };
});
</script>

<template>
    <div v-if="shouldDisplay">
        <!-- Header/Title field -->
        <div v-if="element.type === 'header'" class="mb-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b-2 border-indigo-500 pb-2">
                {{ element.label?.[currentLang] }}
            </h3>
        </div>

        <!-- Group input -->
        <div v-else-if="element.type === 'group'" class="mb-4 overflow-hidden rounded-lg border border-primary/20">
            <div class="flex items-center gap-2 bg-primary/5 px-4 py-2.5 dark:bg-primary/10">
                <span v-if="element.icon" class="inline-flex shrink-0 items-center justify-center rounded-md bg-white p-1 shadow-sm">
                    <img v-if="isImageUrl(element.icon)" :src="element.icon" alt="icon" class="h-6 w-6 object-contain" />
                    <SvgIcon v-else :name="element.icon" class="h-5 w-5 text-primary" />
                </span>
                <span class="text-sm font-semibold text-[var(--primary-dark)] dark:text-primary">{{ element.label?.[currentLang] || element.label?.en || 'Group' }}</span>
            </div>
            <div class="space-y-3 border-l-2 border-primary/25 p-4 pl-5">
                <Preview
                    v-for="(child, cIdx) in (element.children || [])"
                    :key="child.id"
                    :element="child"
                    :index="cIdx"
                    :lang="currentLang"
                    :formValues="formValues"
                />
            </div>
        </div>

        <!-- Regular form fields -->
        <template v-else>
            <div class="mb-2">
                <div class="flex items-center justify-between mb-2">
                    <label :for="element.id" class="font-medium">
                        {{ element.label?.[currentLang] }}
                        <span v-if="element.required" class="text-red-500">*</span>
                    </label>
                    <div v-if="lang === undefined" class="inline-flex rounded-md overflow-hidden border border-gray-200 dark:border-gray-700">
                        <button v-for="loc in resolvedLocales"
                                :key="loc"
                                type="button"
                                class="px-2 py-1 text-xs uppercase"
                                :class="activeLang === loc ? 'bg-indigo-650 text-white bg-indigo-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                                @click="activeLang = loc">
                            {{ loc }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Radio input -->
            <div v-if="element.type === 'radio'" class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`" class="space-y-2">
                    <div class="flex flex-wrap gap-4">
                        <div v-for="(radio, index) in inputProps.options" :key="`radio-key-${index}`" class="flex items-center gap-2">
                            <input
                                type="radio"
                                :id="`radio-id-${element.id}-${repeatIdx}-${index}`" 
                                :name="`radio-${(element.label?.[currentLang] || '').replace(/ /g, '_').toLowerCase().trim()}-${repeatIdx}`" 
                                :value="radio.value"
                                class="h-4 w-4 border-gray-300 dark:border-gray-700 text-indigo-655 focus:ring-indigo-500 cursor-pointer"
                                @change="updateFormValue(element.id, radio.value)"
                            />
                            <label :for="`radio-id-${element.id}-${repeatIdx}-${index}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">{{ radio.label }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkbox input -->
            <div v-else-if="element.type === 'checkbox'" class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`" class="flex flex-wrap gap-4 my-3">
                    <div v-for="(field, key) in inputProps.options" :key="key" class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :id="`${field.value}${key}-${repeatIdx}`" 
                            :name="`checkbox-${(element.label?.[currentLang] || '').replace(/ /g, '_').toLowerCase().trim()}-${key}-${repeatIdx}`" 
                            :value="field.value"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 text-indigo-655 focus:ring-indigo-500 cursor-pointer"
                            @change="(e) => {
                                const currentVal = Array.isArray(formValues[element.id]) ? [...formValues[element.id]] : [];
                                const checked = (e.target as HTMLInputElement).checked;
                                let newVal;
                                if (checked) {
                                    newVal = [...currentVal, field.value];
                                } else {
                                    newVal = currentVal.filter(v => v !== field.value);
                                }
                                updateFormValue(element.id, newVal);
                            }"
                        />
                        <label :for="`${field.value}${key}-${repeatIdx}`" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">{{ field.label }}</label>
                    </div>
                </div>
            </div>

            <!-- File input -->
            <div v-else-if="element.type === 'file'" class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`" class="space-y-1">
                    <input
                        type="file"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"
                        @change="(e) => {
                            const files = (e.target as HTMLInputElement).files;
                            updateFormValue(element.id, files ? files[0] : null);
                        }"
                    />
                    <p v-if="element.hints?.[currentLang]" class="text-xs text-gray-500">
                        {{ element.hints[currentLang] }}
                    </p>
                </div>
            </div>

            <!-- Select dropdown -->
            <div v-else-if="element.type === 'select'" class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`">
                    <div class="flex items-center gap-2">
                        <select
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            @change="(e) => updateFormValue(element.id, (e.target as HTMLSelectElement).value)"
                        >
                            <option value="">{{ element.placeholder?.[currentLang] || 'Select an option' }}</option>
                            <option v-for="opt in inputProps.options" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <button
                            v-if="(repeatCounts[element.id] || 1) > (element.repeat_min || 1) && repeatIdx > 1"
                            type="button"
                            @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) - 1"
                            class="px-2 py-1 text-xs bg-red-105 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 rounded transition-colors cursor-pointer"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <button
                    v-if="element.is_repeatable && (!element.repeat_max || (repeatCounts[element.id] || 1) < element.repeat_max)"
                    type="button"
                    @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) + 1"
                    class="px-3 py-2 text-sm bg-blue-105 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 rounded transition-colors cursor-pointer"
                >
                    + Add More
                </button>
            </div>

            <!-- MultiSelect dropdown -->
            <div v-else-if="element.type === 'multiSelect'" class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`">
                    <div class="flex items-center gap-2">
                        <select
                            multiple
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            @change="(e) => {
                                const selected = Array.from((e.target as HTMLSelectElement).selectedOptions).map(o => o.value);
                                updateFormValue(element.id, selected);
                            }"
                        >
                            <option v-for="opt in inputProps.options" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <button
                            v-if="(repeatCounts[element.id] || 1) > (element.repeat_min || 1) && repeatIdx > 1"
                            type="button"
                            @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) - 1"
                            class="px-2 py-1 text-xs bg-red-105 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 rounded transition-colors cursor-pointer"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <button
                    v-if="element.is_repeatable && (!element.repeat_max || (repeatCounts[element.id] || 1) < element.repeat_max)"
                    type="button"
                    @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) + 1"
                    class="px-3 py-2 text-sm bg-blue-105 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 rounded transition-colors cursor-pointer"
                >
                    + Add More
                </button>
            </div>

            <!-- Textarea -->
            <div v-else-if="element.type === 'textarea'" class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`">
                    <div class="flex items-center gap-2">
                        <textarea
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            :rows="inputProps.rows || 4"
                            :placeholder="inputProps.placeholder"
                            :value="formValues[element.id]"
                            @input="(e) => updateFormValue(element.id, (e.target as HTMLTextAreaElement).value)"
                        ></textarea>
                        <button
                            v-if="(repeatCounts[element.id] || 1) > (element.repeat_min || 1) && repeatIdx > 1"
                            type="button"
                            @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) - 1"
                            class="px-2 py-1 text-xs bg-red-105 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 rounded transition-colors cursor-pointer"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <button
                    v-if="element.is_repeatable && (!element.repeat_max || (repeatCounts[element.id] || 1) < element.repeat_max)"
                    type="button"
                    @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) + 1"
                    class="px-3 py-2 text-sm bg-blue-105 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 rounded transition-colors cursor-pointer"
                >
                    + Add More
                </button>
            </div>

            <!-- Regular inputs (text, email, number, date) -->
            <div v-else class="mb-4 space-y-3">
                <div v-for="(repeatIdx) in (repeatCounts[element.id] || 1)" :key="`repeat-${repeatIdx}`">
                    <div class="flex items-center gap-2">
                        <input
                            :type="element.type === 'date' ? 'date' : element.type"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            :placeholder="inputProps.placeholder"
                            :value="formValues[element.id]"
                            @input="(e) => updateFormValue(element.id, (e.target as HTMLInputElement).value)"
                        />
                        <button
                            v-if="(repeatCounts[element.id] || 1) > (element.repeat_min || 1) && repeatIdx > 1"
                            type="button"
                            @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) - 1"
                            class="px-2 py-1 text-xs bg-red-105 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 rounded transition-colors cursor-pointer"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <button
                    v-if="element.is_repeatable && (!element.repeat_max || (repeatCounts[element.id] || 1) < element.repeat_max)"
                    type="button"
                    @click="repeatCounts[element.id] = (repeatCounts[element.id] || 1) + 1"
                    class="px-3 py-2 text-sm bg-blue-105 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 rounded transition-colors cursor-pointer"
                >
                    + Add More
                </button>
            </div>
        </template>
    </div>
</template>

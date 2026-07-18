<script setup lang="ts">
import SvgIcon from './SvgIcon.vue';
import { ref, watch, computed } from "vue";
import {
    getPrimaryOptionValues,
    getOptionLists,
    hasOptionsFieldType,
    syncOptionLists,
    getConfigLocales,
    type FormElementOptions,
} from '../utils/formOptions';

// Types & Interfaces
interface LocalizedText extends Record<string, string> {}

interface FormElement {
    id: string;
    type: string;
    label: LocalizedText;
    placeholder: LocalizedText;
    hints?: LocalizedText;
    icon?: string;
    icon_path?: string;
    required: boolean;
    has_action: boolean;
    options: FormElementOptions;
    correct_answer?: string[];
    marks?: number;
    condition_input_id?: string | number | null;
    condition_value?: string;
    is_repeatable?: boolean;
    repeat_min?: number;
    repeat_max?: number | null;
}

interface Props {
    element: FormElement;
    errors?: Record<string, string>;
    index: number;
    previewMode?: boolean;
    allFormElements?: FormElement[];
    canMoveUp?: boolean;
    canMoveDown?: boolean;
    isGradableType?: boolean;
    embedded?: boolean;
    collapseAll?: boolean;
    locales?: string[];
}

interface IconMap {
    [key: string]: string;
}

// Props & Emits
const props = defineProps<Props>();
const emit = defineEmits<{
    update: [id: string, field: string, value: any];
    remove: [id: string];
    reorder: [];
    moveUp: [id: string];
    moveDown: [id: string];
}>();

// Constants
const iconMap: IconMap = {
    text: 'mdi:text-short',
    email: 'mdi:text-short',
    number: 'mdi:numeric',
    date: 'mdi:calendar',
    checkbox: 'mdi:checkbox-marked',
    radio: 'mdi:radiobox-marked',
    select: 'mdi:arrow-down-drop-circle',
    textarea: 'mdi:text-long',
    file: 'mdi:file-upload',
    header: 'mdi:text-short',
    group: 'mdi:group',
};

// Reactive State
const isCollapsed = ref<boolean>(false);
const iconComponent = iconMap[props.element.type] || 'mdi:plus';
const isUploading = ref<boolean>(false);
const previewImgUrl = ref<string>('');

watch(
    () => props.collapseAll,
    (collapsed) => {
        isCollapsed.value = collapsed ?? false;
    },
    { immediate: true },
);

const resolvedLocales = computed(() => props.locales || getConfigLocales());

const displayLabel = computed(() => {
    const locales = resolvedLocales.value;
    const primaryLoc = locales[0] || 'en';
    const label = props.element.label?.[primaryLoc]?.trim();
    if (label) {
        return label;
    }

    const type = props.element.type;
    return type.charAt(0).toUpperCase() + type.slice(1);
});

const typeLabel = computed(() => {
    const type = props.element.type;
    if (type === 'group') {
        return 'Group';
    }
    if (type === 'header') {
        return 'Section header';
    }
    return type.charAt(0).toUpperCase() + type.slice(1);
});

const hasOptionsType = computed(() => hasOptionsFieldType(props.element.type));

const optionLists = computed(() => getOptionLists(props.element.options, resolvedLocales.value));

function isImageUrl(val?: string): boolean {
    if (!val) return false;
    try {
        const u = new URL(val);
        return /\.(png|jpg|jpeg|gif|svg|webp)$/i.test(u.pathname);
    } catch {
        return false;
    }
}

// Handle file upload for icon
const handleIconUpload = async (event: any, element: FormElement) => {
    const file = event.files?.[0];
    if (!file) return;

    isUploading.value = true;

    const axios = (window as any).axios;
    const formData = new FormData();
    formData.append('file', file);
    formData.append('oldPath', element.icon_path || '');

    // Get CSRF and router logic from host context or window
    const route = (window as any).route || ((name: string) => {
        return `/api/v1/setup/dynamic-form/upload-file`;
    });

    axios
        .post(route('api.v1.setup.dynamic-form.upload-file'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })
        .then((response: any) => {
            if (response.data.path) {
                emitUpdate('icon', response.data.path);
                previewImgUrl.value = response.data.url;
            }
        })
        .catch((error: any) => {
            console.error('Icon upload error:', error);
        })
        .finally(() => {
            isUploading.value = false;
        });
};

// Functions
function emitUpdate(field: string, value: any): void {
    emit('update', props.element.id, field, value);
}

function updateOptionRow(
    lang: string,
    rowIndex: number,
    label: string,
): void {
    const locales = resolvedLocales.value;
    const lists: Record<string, any[]> = {};
    locales.forEach(loc => {
        lists[loc] = (optionLists.value[loc] || []).map((option) => ({ ...option }));
    });

    const primaryLoc = locales[0] || 'en';
    const previousValue = lists[primaryLoc]?.[rowIndex]?.value?.trim() ?? '';

    if (lists[lang] && lists[lang][rowIndex]) {
        lists[lang][rowIndex].label = label;
    }

    const syncedOptions = syncOptionLists(lists, locales);
    const nextValue = syncedOptions[primaryLoc]?.[rowIndex]?.value?.trim() ?? '';

    emitUpdate('options', syncedOptions);

    if (lang === primaryLoc && previousValue && previousValue !== nextValue) {
        const current = (props.element.correct_answer ?? []) as string[];
        if (current.includes(previousValue)) {
            emitUpdate(
                'correct_answer',
                current.map((answer) =>
                    answer === previousValue ? nextValue : answer,
                ),
            );
        }
    }
}

function addOptionRow(): void {
    const locales = resolvedLocales.value;
    const primaryLoc = locales[0] || 'en';
    const nextIndex = (optionLists.value[primaryLoc] || []).length + 1;
    const label = `Option ${nextIndex}`;

    const newLists: Record<string, any[]> = {};
    locales.forEach(loc => {
        const current = optionLists.value[loc] || [];
        newLists[loc] = [...current, { label, value: '' }];
    });

    emitUpdate('options', syncOptionLists(newLists, locales));
}

function removeOptionRow(rowIndex: number): void {
    const locales = resolvedLocales.value;
    const primaryLoc = locales[0] || 'en';
    const primaryCount = (optionLists.value[primaryLoc] || []).length;
    if (primaryCount <= 1) {
        return;
    }

    const removedValue = optionLists.value[primaryLoc]?.[rowIndex]?.value?.trim() ?? '';

    const newLists: Record<string, any[]> = {};
    locales.forEach(loc => {
        const current = optionLists.value[loc] || [];
        newLists[loc] = current.filter((_, index) => index !== rowIndex);
    });

    emitUpdate('options', syncOptionLists(newLists, locales));

    if (removedValue) {
        const current = (props.element.correct_answer ?? []) as string[];
        const updated = current.filter((answer) => answer !== removedValue);

        if (updated.length !== current.length) {
            emitUpdate('correct_answer', updated);
        }
    }
}

// Get list of available form elements (excluding current element)
const availableFormElements = computed(() => {
    const locales = resolvedLocales.value;
    const primaryLoc = locales[0] || 'en';
    return (props.allFormElements || [])
        .filter((el) => el.id !== props.element.id && el.type !== 'group')
        .map((el) => ({
            id: el.id,
            label: `${el.label?.[primaryLoc] || el.type} (${el.type})`,
        }));
});

// Get options from the selected condition field
const getConditionFieldOptions = computed(() => {
    if (!props.element.condition_input_id || !props.allFormElements) {
        return [];
    }

    const conditionField = props.allFormElements.find(
        (el) => el.id === props.element.condition_input_id
    );

    if (!conditionField?.options) {
        return [];
    }

    return getPrimaryOptionValues(conditionField.options, resolvedLocales.value);
});

// Parse options for correct answer selection
const parsedOptionsForCorrectAnswer = computed(() => {
    const primaryLoc = resolvedLocales.value[0] || 'en';
    return (optionLists.value[primaryLoc] || []).filter(
        (option) => option.label.trim() && option.value.trim(),
    );
});

// Single correct answer (radio/select)
const singleCorrectAnswer = computed(() => {
    return (props.element.correct_answer ?? [])[0] ?? null;
});

function onSingleCorrectAnswerChange(val: string | null): void {
    emitUpdate('correct_answer', val ? [val] : []);
}

function toggleCorrectAnswer(opt: string, checked: boolean): void {
    const current = (props.element.correct_answer ?? []) as string[];
    const updated = checked
        ? [...current.filter((v) => v !== opt), opt]
        : current.filter((v) => v !== opt);
    emitUpdate('correct_answer', updated);
}

// Normalize backend numeric/string values (0/1, '0'/'1') to booleans
watch(
    () => props.element,
    (el) => {
        if (!el) return;
        // Coerce to boolean reliably
        (el as any).required = !!Number((el as any).required);
        (el as any).has_action = !!Number((el as any).has_action);
    },
    { immediate: true }
);
</script>

<template>
    <div
        :class="[
            embedded
                ? 'rounded-md border border-border/60 bg-background'
                : 'rounded-lg border border-border bg-card shadow-sm transition-shadow hover:border-primary/20 hover:shadow-md',
        ]"
    >
        <div
            class="flex items-center justify-between gap-3"
            :class="embedded ? 'px-3 py-2.5' : 'px-4 py-3'"
        >
            <div class="flex min-w-0 flex-1 items-center gap-2.5">
                <div
                    class="flex shrink-0 cursor-grab items-center text-muted-foreground active:cursor-grabbing"
                >
                    <SvgIcon
                        name="mdi:cursor-move"
                        class="h-4 w-4"
                    />
                </div>
                <span
                    v-if="!embedded"
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[11px] font-semibold text-primary"
                >
                    {{ index + 1 }}
                </span>
                <span
                    class="inline-flex shrink-0 items-center justify-center rounded-md bg-primary/10 p-1.5"
                >
                    <SvgIcon
                        :name="iconComponent"
                        class="h-4 w-4 text-primary"
                    />
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">
                        {{ displayLabel }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ typeLabel }}
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-1">
                <button
                    v-if="!embedded && props.canMoveUp !== false"
                    type="button"
                    @click="$emit('moveUp', element.id)"
                    class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-primary disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer"
                    :disabled="props.index === 0"
                    title="Move up"
                >
                    <SvgIcon
                        name="mdi:arrow-up"
                        class="h-4 w-4"
                    />
                </button>
                <button
                    v-if="!embedded && props.canMoveDown !== false"
                    type="button"
                    @click="$emit('moveDown', element.id)"
                    class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-primary disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer"
                    :disabled="props.canMoveDown === false"
                    title="Move down"
                >
                    <SvgIcon
                        name="mdi:arrow-down"
                        class="h-4 w-4"
                    />
                </button>
                <button
                    type="button"
                    @click="isCollapsed = !isCollapsed"
                    class="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground cursor-pointer"
                    :title="isCollapsed ? 'Expand' : 'Collapse'"
                >
                    <SvgIcon
                        :name="isCollapsed ? 'mdi:chevron-down' : 'mdi:chevron-up'"
                        class="h-4 w-4"
                    />
                </button>
                <button
                    type="button"
                    @click="$emit('remove', element.id)"
                    class="rounded p-1 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 cursor-pointer"
                    title="Remove"
                >
                    <SvgIcon
                        name="mdi:delete"
                        class="h-4 w-4"
                    />
                </button>
            </div>
        </div>
        <div
            v-show="!isCollapsed"
            class="space-y-3 border-t border-border/60"
            :class="embedded ? 'px-3 py-3' : 'px-4 py-4'"
        >
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300"> Label </label>
                <div class="mt-1 grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div v-for="loc in resolvedLocales" :key="loc" class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-semibold select-none uppercase">
                            {{ loc }}
                        </span>
                        <input
                            :id="`label-${loc}-${index}`"
                            type="text"
                            class="flex-1 min-w-0 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-r-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            v-model="element.label[loc]"
                            @input="emitUpdate('label', { ...element.label })"
                        />
                    </div>
                </div>
            </div>
            <div v-if="element.type !== 'group' && element.type !== 'header'">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300"> Placeholder </label>
                <div class="mt-1 grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div v-for="loc in resolvedLocales" :key="loc" class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-semibold select-none uppercase">
                            {{ loc }}
                        </span>
                        <input
                            :id="`placeholder-${loc}-${index}`"
                            type="text"
                            class="flex-1 min-w-0 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-r-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            v-model="element.placeholder[loc]"
                            @input="emitUpdate('placeholder', { ...element.placeholder })"
                        />
                    </div>
                </div>
            </div>
            <div v-if="hasOptionsType" class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300"> Options </label>
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-primary/20 px-2.5 py-1.5 text-xs font-medium text-primary transition-colors hover:bg-primary/5 cursor-pointer"
                        @click="addOptionRow"
                    >
                        <SvgIcon name="mdi:plus" class="h-4 w-4" />
                        Add More
                    </button>
                </div>

                <div class="space-y-2">
                    <div
                        v-for="(_, rowIndex) in (optionLists[resolvedLocales[0] || 'en'] || [])"
                        :key="`${element.id}-option-${rowIndex}`"
                        class="flex items-center gap-2"
                    >
                        <div class="grid flex-1 grid-cols-1 gap-2 md:grid-cols-2">
                            <div v-for="loc in resolvedLocales" :key="loc" class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-semibold select-none uppercase">
                                    {{ loc }}
                                </span>
                                <input
                                    :id="`options-${loc}-${index}-${rowIndex}`"
                                    type="text"
                                    class="flex-1 min-w-0 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-r-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                                    :value="optionLists[loc]?.[rowIndex]?.label ?? ''"
                                    :placeholder="`Option ${rowIndex + 1}`"
                                    @input="(e) => updateOptionRow(loc, rowIndex, (e.target as HTMLInputElement).value || '')"
                                />
                            </div>
                        </div>

                        <button
                            v-if="(optionLists[resolvedLocales[0] || 'en'] || []).length > 1"
                            type="button"
                            class="shrink-0 rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/20 cursor-pointer"
                            :aria-label="`Remove option ${rowIndex + 1}`"
                            @click="removeOptionRow(rowIndex)"
                        >
                            <SvgIcon
                                name="mdi:delete"
                                class="h-4 w-4"
                            />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Points/Marks (Quiz & Assessment) -->
            <div v-if="isGradableType && element.type !== 'group' && element.type !== 'header'"
                class="flex items-center gap-2">
                <label :for="`marks-${index}`" class="text-xs font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">Points:</label>
                <input
                    :id="`marks-${index}`"
                    type="number"
                    min="1"
                    class="w-20 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    :value="element.marks ?? 1"
                    @input="emitUpdate('marks', Math.max(1, parseInt(($event.target as HTMLInputElement).value) || 1))"
                />
            </div>

            <!-- Correct Answer (Quiz & Assessment) -->
            <div v-if="isGradableType && hasOptionsType"
                class="border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 rounded-md p-3 space-y-3">
                <label class="block text-sm font-semibold text-green-700 dark:text-green-400">
                    ✓ Correct Answer <span class="text-red-500">*</span>
                    <span class="ml-1 text-xs font-normal text-gray-500 dark:text-gray-400">
                        {{ ['radio', 'select'].includes(element.type) ? '(pick one)' : '(pick all that apply)' }}
                    </span>
                </label>

                <div v-if="parsedOptionsForCorrectAnswer.length === 0" class="text-xs text-gray-400 italic">
                    Add options above to select correct answers.
                </div>

                <!-- Chip group -->
                <div v-else class="flex flex-wrap gap-2">
                    <button
                        v-for="opt in parsedOptionsForCorrectAnswer"
                        :key="opt.value"
                        type="button"
                        @click="
                            ['radio', 'select'].includes(element.type)
                                ? onSingleCorrectAnswerChange(singleCorrectAnswer === opt.value ? null : opt.value)
                                : toggleCorrectAnswer(opt.value, !(element.correct_answer ?? []).includes(opt.value))
                        "
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium transition-all cursor-pointer',
                            (element.correct_answer ?? []).includes(opt.value)
                                ? 'border-green-500 bg-green-600 text-white shadow-sm dark:border-green-400 dark:bg-green-600'
                                : 'border-gray-300 bg-white text-gray-700 hover:border-green-400 hover:bg-green-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-green-500 dark:hover:bg-green-900/30',
                        ]"
                    >
                        <SvgIcon
                            v-if="(element.correct_answer ?? []).includes(opt.value)"
                            name="mdi:check-all"
                            class="w-3.5 h-3.5 shrink-0"
                        />
                        {{ opt.label }}
                    </button>
                </div>
            </div>

            <div v-if="element.type === 'file'">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300"> Hints/Note </label>
                <div class="mt-1 grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div v-for="loc in resolvedLocales" :key="loc" class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-semibold select-none uppercase">
                            {{ loc }}
                        </span>
                        <input
                            :id="`hints-${loc}-${index}`"
                            type="text"
                            class="flex-1 min-w-0 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-r-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            :value="element.hints?.[loc] ?? ''"
                            @input="(e) => {
                                const hints = { ...(element.hints || {}) };
                                hints[loc] = (e.target as HTMLInputElement).value || '';
                                emitUpdate('hints', hints);
                            }"
                        />
                    </div>
                </div>
            </div>
            <template v-if="element.type !== 'group' && element.type !== 'header'">
                <div class="flex flex-wrap items-center gap-4 py-1">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :id="`required-${index}`"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 text-primary focus:ring-primary cursor-pointer"
                            v-model="element.required"
                            @change="emitUpdate('required', !!element.required)"
                        />
                        <label :for="`required-${index}`" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer select-none"> Required field </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :id="`has_action-${index}`"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 text-primary focus:ring-primary cursor-pointer"
                            v-model="element.has_action"
                            @change="emitUpdate('has_action', !!element.has_action)"
                        />
                        <label :for="`has_action-${index}`" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer select-none"> Has Action </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :id="`is_repeatable-${index}`"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 text-primary focus:ring-primary cursor-pointer"
                            v-model="element.is_repeatable"
                            @change="emitUpdate('is_repeatable', !!element.is_repeatable)"
                        />
                        <label :for="`is_repeatable-${index}`" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer select-none"> Allow "Add More" (Repeatable) </label>
                    </div>
                </div>

                <!-- Repeat Min/Max -->
                <div v-if="element.is_repeatable" class="grid grid-cols-2 gap-2 pl-3 border-l-2 border-gray-200 dark:border-gray-700">
                    <div>
                        <label class="block text-xs font-semibold text-gray-650 dark:text-gray-400">Min Repeat</label>
                        <input
                            type="number"
                            v-model.number="element.repeat_min"
                            min="1"
                            @change="emitUpdate('repeat_min', element.repeat_min)"
                            class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-650 dark:text-gray-400">Max Repeat (leave empty for unlimited)</label>
                        <input
                            type="number"
                            v-model.number="element.repeat_max"
                            min="1"
                            @change="emitUpdate('repeat_max', element.repeat_max || null)"
                            class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                        />
                    </div>
                </div>

                <!-- Conditional Display -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Conditional Display <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400 mb-2">(Show this field only when another field has a specific value)</span></label>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Depends on field:</label>
                            <select
                                v-model="element.condition_input_id"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                                @change="emitUpdate('condition_input_id', element.condition_input_id || null)"
                            >
                                <option :value="null">Select a field (optional)</option>
                                <option v-for="item in availableFormElements" :key="item.id" :value="item.id">
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>

                        <div v-if="element.condition_input_id && getConditionFieldOptions.length > 0">
                            <label class="block text-[11px] text-gray-500 mb-1">Show when value is:</label>
                            <select
                                v-model="element.condition_value"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                                @change="emitUpdate('condition_value', element.condition_value || '')"
                            >
                                <option value="">Select value</option>
                                <option v-for="val in getConditionFieldOptions" :key="val" :value="val">
                                    {{ val }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </template>

            <div v-if="element.type === 'group'">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3"> Image or Icon (optional) </label>

                <div class="space-y-3">
                    <!-- File Upload -->
                    <div>
                        <small class="block mb-2 text-gray-500">Upload an image file (Max 2MB)</small>
                        <div class="relative flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900/20 hover:bg-gray-100 dark:hover:bg-gray-900/30 transition-colors">
                            <input
                                type="file"
                                accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                @change="(e) => {
                                    const files = (e.target as HTMLInputElement).files;
                                    if (files && files.length > 0) {
                                        handleIconUpload({ files }, element);
                                    }
                                }"
                            />
                            <div class="text-center pointer-events-none">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Click to upload or drag image here
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-850 rounded">
                        <span class="text-xs text-gray-500">Preview:</span>
                        <template v-if="element.icon">
                            <img v-if="isImageUrl(element.icon) && !previewImgUrl" :src="element.icon" alt="icon" class="w-10 h-10 object-contain rounded border border-gray-200 bg-white p-0.5 dark:border-gray-600 dark:bg-gray-700" />
                            <img v-if="previewImgUrl" :src="previewImgUrl" alt="icon" class="w-10 h-10 object-contain rounded border border-gray-200 bg-white p-0.5 dark:border-gray-600 dark:bg-gray-700" />
                            <span v-else class="inline-flex items-center justify-center rounded bg-white p-1 dark:bg-gray-700">
                                <SvgIcon :name="element.icon" class="w-5 h-5 text-primary" />
                            </span>
                        </template>
                        <span v-else class="text-xs text-gray-400">No icon set</span>
                    </div>
                </div>
            </div>

            <div
                v-for="attr in ['label', 'type', 'placeholder', 'options']"
                :key="index + '-' + attr">
                <p v-show="props.errors?.['elements.' + index + '.' + attr]" class="mt-1 text-xs text-red-600 dark:text-red-400">
                    {{ props.errors?.['elements.' + index + '.' + attr] }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import SvgIcon from './SvgIcon.vue';

interface TypeData {
    option: string;
    value: string;
}

interface Props {
    form: Record<string, any>;
    types: TypeData[];
    isLoading: boolean;
    isEditing: boolean;
}

const props = defineProps<Props>();

const isQuizType = computed(() => props.form.type === 'Quiz');
const isSurveyType = computed(() => props.form.type === 'Survey');

function resetQuizFields(): void {
    props.form.quiz_title = null;
    props.form.quiz_description = null;
    props.form.passing_score = 70;
    props.form.max_attempts = null;
    props.form.time_limit_minutes = null;
    props.form.shuffle_questions = false;
    props.form.shuffle_options = false;
    props.form.show_result_immediately = true;
    props.form.quiz_is_active = true;
}

const resetSurveyFields = (): void => {
    props.form.description = null;
    props.form.end_at = null;
};

function handleTypeChange(): void {
    if (props.form.type !== 'Quiz') {
        resetQuizFields();
    }
    if (props.form.type !== 'Survey') {
        resetSurveyFields();
    }
}
</script>

<template>
    <div class="grid grid-cols-1 gap-6 pt-6 md:grid-cols-2">
        <!-- Form Type -->
        <div>
            <div v-if="isLoading" class="mb-2">
                <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                <div class="h-10 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
            </div>
            <div v-else>
                <label for="type" class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Form Type <span class="text-red-500">*</span>
                </label>
                <select
                    id="type"
                    v-model="form.type"
                    class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    @change="handleTypeChange"
                >
                    <option value="">Select a Type</option>
                    <option v-for="t in types" :key="t.value" :value="t.value">{{ t.option }}</option>
                </select>
                <p v-if="form.errors.type" class="mt-2 text-xs text-red-650 text-red-600 dark:text-red-450">
                    {{ form.errors.type }}
                </p>
            </div>
        </div>

        <!-- Form Name -->
        <div>
            <div v-if="isLoading" class="mb-2">
                <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                <div class="h-10 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
            </div>
            <div v-else>
                <label for="name" class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Form Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    placeholder="Enter Form Name"
                    v-model="form.name"
                />
                <p v-if="form.errors.name" class="mt-2 text-xs text-red-650 text-red-600 dark:text-red-450">
                    {{ form.errors.name }}
                </p>
            </div>
        </div>

        <!-- Material (Quiz only) -->


        <!-- Survey only -->
        <template v-if="isSurveyType">
            <div class="col-span-full">
                <div v-if="isLoading" class="mb-2">
                    <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                    <div class="h-20 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
                </div>
                <div v-else>
                    <label for="description" class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                        >Description <span class="text-red-500">*</span></label
                    >
                    <textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Description"
                        rows="3"
                        class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-2 text-xs text-red-655 text-red-600 dark:text-red-450">
                        {{ form.errors.description }}
                    </p>
                </div>
            </div>
            <div class="md:col-span-full lg:col-span-1">
                <div v-if="isLoading" class="mb-2">
                    <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                    <div class="h-10 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
                </div>
                <div v-else>
                    <label for="end_at" class="text-xs font-semibold text-gray-700 dark:text-gray-300">End At</label>
                    <input
                        type="datetime-local"
                        id="end_at"
                        v-model="form.end_at"
                        class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    />
                    <p v-if="form.errors.end_at" class="mt-2 text-xs text-red-655 text-red-600 dark:text-red-450">
                        {{ form.errors.end_at }}
                    </p>
                </div>
            </div>
        </template>

        <!-- Status (edit only) -->
        <div
            v-if="
                isEditing &&
                (form.status === 'Approved' ||
                    form.status === 'Inactive' ||
                    form.status === 'Active')
            "
        >
            <div v-if="isLoading">
                <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                <div class="h-10 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
            </div>
            <div v-else>
                <label for="status" class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Status <span class="text-red-500">*</span>
                </label>
                <div class="mt-3 flex flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <input
                            type="radio"
                            v-model="form.status"
                            id="status-active"
                            name="status"
                            value="Active"
                            class="h-4 w-4 border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <label for="status-active" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">Active</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="radio"
                            v-model="form.status"
                            id="status-inactive"
                            name="status"
                            value="Inactive"
                            class="h-4 w-4 border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <label for="status-inactive" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                            Inactive
                        </label>
                    </div>
                </div>
                <p v-if="form.errors.status" class="mt-2 text-xs text-red-655 text-red-600 dark:text-red-450">
                    {{ form.errors.status }}
                </p>
            </div>
        </div>

        <!-- Quiz Settings Panel -->
        <template v-if="isQuizType">
            <div class="col-span-1 md:col-span-2">
                <div
                    class="space-y-5 rounded-xl border border-blue-200 bg-white p-5 dark:border-blue-700 dark:bg-blue-900/20"
                >
                    <h4
                        class="text-primary flex items-center gap-2 text-sm font-semibold dark:text-blue-300"
                    >
                        <SvgIcon name="mdi:help-circle" class="h-4 w-4" />
                        Quiz Settings
                    </h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Quiz Title -->
                        <div>
                            <label
                                class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                            >
                                Quiz Title
                                <span class="text-[10px] font-normal text-gray-400"
                                    >(optional override)</span
                                >
                            </label>
                            <input
                                type="text"
                                v-model="form.quiz_title"
                                placeholder="e.g. Chapter 1 Quiz"
                                class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            />
                            <p v-if="form.errors.quiz_title" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.quiz_title }}
                            </p>
                        </div>

                        <!-- Passing Score -->
                        <div>
                            <label
                                class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                            >
                                Passing Score (%)
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    v-model.number="form.passing_score"
                                    class="block w-full pr-10 px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                                />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            <p v-if="form.errors.passing_score" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.passing_score }}
                            </p>
                        </div>

                        <!-- Max Attempts -->
                        <div>
                            <label
                                class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                            >
                                Max Attempts
                                <span class="text-[10px] font-normal text-gray-400"
                                    >(leave empty = unlimited)</span
                                >
                            </label>
                            <input
                                type="number"
                                min="1"
                                v-model.number="form.max_attempts"
                                placeholder="Unlimited"
                                class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            />
                            <p v-if="form.errors.max_attempts" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.max_attempts }}
                            </p>
                        </div>

                        <!-- Time Limit -->
                        <div>
                            <label
                                class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                            >
                                Time Limit (minutes)
                                <span class="text-[10px] font-normal text-gray-400"
                                    >(leave empty = no limit)</span
                                >
                            </label>
                            <input
                                type="number"
                                min="1"
                                v-model.number="form.time_limit_minutes"
                                placeholder="No limit"
                                class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            />
                            <p v-if="form.errors.time_limit_minutes" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                {{ form.errors.time_limit_minutes }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label
                                class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                                >Description</label
                            >
                            <textarea
                                v-model="form.quiz_description"
                                rows="3"
                                placeholder="Brief description of this quiz..."
                                class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Toggles -->
                    <div class="grid grid-cols-1 gap-4 pt-1 sm:grid-cols-2">
                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold text-gray-750 dark:text-gray-300"
                                >
                                    Shuffle Questions
                                </p>
                                <p
                                    class="text-[10px] text-gray-500 dark:text-gray-405"
                                >
                                    Randomize question order
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.shuffle_questions" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold text-gray-755 dark:text-gray-300"
                                >
                                    Shuffle Options
                                </p>
                                <p
                                    class="text-[10px] text-gray-500 dark:text-gray-405"
                                >
                                    Randomize answer choices
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.shuffle_options" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold text-gray-755 dark:text-gray-300"
                                >
                                    Show Result Immediately
                                </p>
                                <p
                                    class="text-[10px] text-gray-500 dark:text-gray-450"
                                >
                                    Show score right after submission
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.show_result_immediately" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold text-gray-755 dark:text-gray-300"
                                >
                                    Active
                                </p>
                                <p
                                    class="text-[10px] text-gray-500 dark:text-gray-450"
                                >
                                    Allow learners to take this quiz
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.quiz_is_active" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

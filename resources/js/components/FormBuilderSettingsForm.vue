<script setup lang="ts">
import Label from './Label.vue';
import Input from './Input.vue';
import Select from './Select.vue';

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

defineProps<Props>();
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
                <Label for="type" :required="true">Form Type</Label>
                <Select
                    id="type"
                    v-model="form.type"
                    class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                >
                    <option value="">Select a Type</option>
                    <option v-for="t in types" :key="t.value" :value="t.value">{{ t.option }}</option>
                </Select>
                <p v-if="form.errors.type" class="mt-2 text-xs text-red-600 dark:text-red-400">
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
                <Label for="name" :required="true">Form Name</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                    placeholder="Enter Form Name"
                    v-model="form.name"
                />
                <p v-if="form.errors.name" class="mt-2 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.name }}
                </p>
            </div>
        </div>

        <!-- Description -->
        <div class="col-span-full">
            <div v-if="isLoading" class="mb-2">
                <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                <div class="h-20 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
            </div>
            <div v-else>
                <Label for="description">Description</Label>
                <textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Description"
                    rows="3"
                    class="mt-1 block w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                ></textarea>
                <p v-if="form.errors.description" class="mt-2 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.description }}
                </p>
            </div>
        </div>

        <!-- Status -->
        <div>
            <div v-if="isLoading">
                <div class="h-5 w-24 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-2"></div>
                <div class="h-10 w-full bg-slate-200 dark:bg-slate-700 animate-pulse rounded-lg"></div>
            </div>
            <div v-else>
                <Label for="status" :required="true">Status</Label>
                <div class="mt-3 flex flex-wrap gap-4">
                    <div class="flex items-center gap-2">
                        <input
                            type="radio"
                            v-model="form.status"
                            id="status-pending"
                            name="status"
                            value="Pending"
                            class="h-4 w-4 border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <Label for="status-pending" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">Pending</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="radio"
                            v-model="form.status"
                            id="status-active"
                            name="status"
                            value="Active"
                            class="h-4 w-4 border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <Label for="status-active" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">Active</Label>
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
                        <Label for="status-inactive" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">Inactive</Label>
                    </div>
                </div>
                <p v-if="form.errors.status" class="mt-2 text-xs text-red-600 dark:text-red-400">
                    {{ form.errors.status }}
                </p>
            </div>
        </div>
    </div>
</template>

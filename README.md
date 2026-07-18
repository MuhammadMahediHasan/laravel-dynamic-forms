# Laravel Dynamic Forms

A highly decoupled, event-driven, layout-agnostic dynamic form builder package for Laravel and Vue 3.

---

## Features
- **Decoupled Architecture**: All UI alerts, page layouts, toast notifications, relationships, and navigation logic are delegated to the host application.
- **Tailwind CSS Ready**: Clean Tailwind markup with a customizable `ldf-*` CSS class prefix namespace.
- **Multiple Locales**: Configuration-driven multi-locale translation inputs (defaults to `en`).
- **Interactive Drag & Drop**: Easy elements library ordering powered by `vuedraggable`.

---

## Installation

### 1. Require the Package via Composer
```bash
composer require muhammadmahedihasan/laravel-dynamic-forms
```

### 2. Run the Install Command
The install command will automatically publish the package configuration, publish the Vue builder assets into your host application (`resources/js/vendor/dynamic-forms`), and inspect your `package.json` file to inject the required `vuedraggable` peer dependency:
```bash
php artisan dynamic-forms:install
```

### 3. Rebuild Your Assets
Compile the published frontend assets using your asset pipeline (e.g., Vite):
```bash
npm install
npm run build
```

---

## Vue 3 Integration Example

Since the form builder is fully decoupled, you should wrap it inside a page component in your host application. This allows you to wrap the form builder in your main layout, control the button loading states, perform API requests, and show custom toast notifications (e.g., using PrimeVue Toast).

Here is a complete, real-world wrapper component example (`resources/js/pages/dashboard/setup/dynamic-form/Create.vue`):

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

// Import the decoupled form builder component from the vendor resources directory
import FormBuilder from '@/vendor/dynamic-forms/Create.vue';

interface Props {
    types?: any[];
    status?: any[];
    editData?: any;
    availableElements?: any[];
    presetType?: string;
}

const props = defineProps<Props>();
const toast = useToast();
const isProcessing = ref(false);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Forms', href: '/dashboard/setup/dynamic-form' },
    { title: 'Create Form', href: '/dashboard/setup/dynamic-form/create' },
];

// 1. Handle Form Submission
const handleSubmit = (payload: { form: any; elements: any[] }) => {
    isProcessing.value = true;
    
    // Select post/put endpoint and payload structure
    const isEdit = !!props.editData?.form_id;
    const url = isEdit 
        ? `/dashboard/setup/dynamic-form/${props.editData.form_id}` 
        : '/dashboard/setup/dynamic-form';
        
    const method = isEdit ? 'put' : 'post';

    router[method](
        url,
        {
            ...payload.form,
            elements: payload.elements,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: isEdit ? 'Form updated successfully' : 'Form created successfully',
                    life: 3000,
                });
            },
            onError: (errors) => {
                Object.values(errors).forEach((message) => {
                    toast.add({
                        severity: 'error',
                        summary: 'Validation Error',
                        detail: String(message),
                        life: 4000,
                    });
                });
            },
            onFinish: () => {
                isProcessing.value = false;
            },
        }
    );
};

// 2. Handle Navigation / Redirection
const handleCancel = () => {
    router.get('/dashboard/setup/dynamic-form');
};

// 3. Handle Form Builder Errors (e.g. empty element submissions)
const handleError = (message: string) => {
    toast.add({
        severity: 'warn',
        summary: 'Warning',
        detail: message,
        life: 4000,
    });
};
</script>

<template>
    <Head title="Configure Dynamic Form" />
    <Toast />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #pageHeader>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Configure Form Builder
            </h2>
        </template>

        <!-- Use the Form Builder component and bind the events -->
        <FormBuilder
            v-bind="props"
            :is-processing="isProcessing"
            @submit="handleSubmit"
            @cancel="handleCancel"
            @error="handleError"
        />
    </AppLayout>
</template>
```

---

## Component API

### Props

| Prop | Type | Required | Description |
|---|---|---|---|
| `types` | `TypeData[]` | No | Available dynamic form types (e.g. `Form`, `Quiz`, `Survey`). |
| `status` | `StatusData[]` | No | Custom status options (e.g. `Draft`, `Published`). |
| `editData` | `EditData` | No | Pre-existing form attributes and elements hierarchy for editing mode. |
| `availableElements` | `AvailableElement[]` | No | Pre-loaded catalog items representing available builder elements. |
| `presetType` | `string` | No | Prefilled default type for new form creations. |
| `isProcessing` | `boolean` | No | Shows loading wheel on the submit button while processing. |

### Events

- **`@submit`**: Emits `{ form: Record<string, any>, elements: any[] }` when the user clicks save.
- **`@cancel`**: Emits when the user triggers the back/go-back button.
- **`@error`**: Emits a `string` validation error when the builder detects schema issues.

---

## Custom Styling

All core components are prefix namespace styled with `ldf-*` classes. Developers can target and customize styles cleanly in their global stylesheet without using `!important` overrides:

```css
/* Custom styling overrides */
.ldf-card {
    border-color: #3b82f6; /* Custom border color */
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
}

.ldf-title {
    font-family: 'Outfit', sans-serif;
    letter-spacing: -0.025em;
}

.ldf-back-btn {
    border-radius: 9999px;
    background-color: #f3f4f6;
}
```

---

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

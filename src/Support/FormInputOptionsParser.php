<?php

namespace MuhammadMahediHasan\Df\Support;

use MuhammadMahediHasan\Df\Models\DynamicFormInput;

class FormInputOptionsParser
{
    public function parseFromDynamicFormInput(DynamicFormInput $formInput): array
    {
        $locales = config('dynamic-forms.locales', ['en']);
        $default = [];
        foreach ($locales as $locale) {
            $default[$locale] = [];
        }

        return $this->parseStructuredOptions(
            $formInput->options,
            ($formInput->input?->type ?? '') === 'checkbox',
        ) ?? $default;
    }

    /**
     * Merge option selections into form-builder options.
     */
    public function applyAssessmentSelections(array $options, mixed $assessmentOptions): array
    {
        $assessmentOptions = JsonArrayDecoder::decode($assessmentOptions);

        if ($assessmentOptions === null) {
            return $options;
        }

        $selectedValues = collect($assessmentOptions)
            ->flatten(1)
            ->where('isSelected', true)
            ->pluck('value')
            ->unique()
            ->values()
            ->all();

        $locales = config('dynamic-forms.locales', ['en']);
        foreach ($locales as $locale) {
            if (isset($options[$locale]) && is_array($options[$locale])) {
                foreach ($options[$locale] as $index => $option) {
                    if (in_array($option['value'], $selectedValues)) {
                        $options[$locale][$index]['isSelected'] = true;
                    }
                }
            }
        }

        return $options;
    }

    private function parseStructuredOptions(mixed $stored, bool $isCheckbox): ?array
    {
        $decoded = JsonArrayDecoder::decode($stored);

        if ($decoded === null && is_array($stored)) {
            $decoded = $stored;
        }

        if (! is_array($decoded)) {
            return null;
        }

        $locales = config('dynamic-forms.locales', ['en']);
        $result = [];

        foreach ($locales as $locale) {
            $localeOptions = $decoded[$locale] ?? null;
            if (is_array($localeOptions)) {
                $result[$locale] = $this->normalizeStoredOptionObjects($localeOptions, $isCheckbox);
            } else {
                $result[$locale] = [];
            }
        }

        return $result;
    }

    /**
     * Normalize options objects format.
     */
    private function normalizeStoredOptionObjects(array $items, bool $isCheckbox): array
    {
        $options = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? $item['value'] ?? ''));
            $value = trim((string) ($item['value'] ?? $item['label'] ?? ''));

            if ($label === '' && $value === '') {
                continue;
            }

            $option = [
                'label' => $label ?: $value,
                'value' => $value ?: $label,
            ];

            if ($isCheckbox) {
                $option['isSelected'] = (bool) ($item['isSelected'] ?? false);
            }

            $options[] = $option;
        }

        return $options;
    }
}

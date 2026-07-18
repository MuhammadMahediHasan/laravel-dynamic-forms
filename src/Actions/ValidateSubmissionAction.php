<?php

namespace MuhammadMahediHasan\Df\Actions;

use MuhammadMahediHasan\Df\Models\DynamicFormInput;
use MuhammadMahediHasan\Df\Support\FormInputOptionsParser;

class ValidateSubmissionAction
{
    public function __construct(
        protected FormInputOptionsParser $optionsParser
    ) {}

    /**
     * Validate submission elements against their form inputs configuration.
     *
     * @return array<string, string> Error key-value pairs
     */
    public function execute(int $dynamicFormId, array $rawItems): array
    {
        $errors = [];
        $formInputs = DynamicFormInput::where('df_form_id', $dynamicFormId)
            ->with('input')
            ->get()
            ->keyBy('id');

        $seenIds = [];

        foreach ($rawItems as $index => $item) {
            $inputId = $item['dynamicFormInputId'] ?? null;

            if (!$inputId) {
                continue;
            }

            if (isset($seenIds[$inputId])) {
                $errors["items.{$index}.dynamicFormInputId"] = 'Duplicate form input.';
                continue;
            }

            $seenIds[$inputId] = true;
            $formInput = $formInputs->get($inputId);

            if (! $formInput) {
                $errors["items.{$index}.dynamicFormInputId"] = 'The selected form input is invalid for this form.';
                continue;
            }

            $component = $formInput->input?->component ?? '';
            if (in_array($component, config('dynamic-forms.value_skipped_components', ['Group', 'Header']), true)) {
                continue;
            }

            $options = $this->optionsParser->parseFromDynamicFormInput($formInput);

            $rawValue = $item['value'] ?? null;
            $isEmpty = ($rawValue === null || $rawValue === '' || (is_array($rawValue) && empty($rawValue)));

            if ($formInput->required && $isEmpty) {
                $errors["items.{$index}.value"] = 'This field is required.';
                continue;
            }

            if ($isEmpty) {
                continue;
            }

            $type = $formInput->input?->type ?? '';
            $validationError = $this->match($type, $rawValue, $options);

            if ($validationError) {
                $errors["items.{$index}.value"] = $validationError;
            }
        }

        return $errors;
    }

    private function match(string $type, mixed $value, array $options): ?string
    {
        return match ($type) {
            'number' => $this->validateNumber($value),
            'email' => $this->validateEmail($value),
            'select', 'radio' => $this->validateSingleChoice($value, $options),
            'checkbox', 'multiSelect' => $this->validateMultiChoice($value, $options),
            default => null,
        };
    }

    private function validateNumber(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return 'Value must be a number.';
        }

        return null;
    }

    private function validateEmail(mixed $value): ?string
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Value must be a valid email address.';
        }

        return null;
    }

    private function validateSingleChoice(mixed $value, array $options): ?string
    {
        $valueStr = trim((string) $value);

        if (! $this->isAllowedOptionValue($options, $valueStr)) {
            return 'The selected value is invalid.';
        }

        return null;
    }

    private function validateMultiChoice(mixed $value, array $options): ?string
    {
        if (! is_array($value)) {
            return 'Value must be an array of selected options.';
        }

        foreach ($value as $item) {
            $itemStr = trim((string) $item);
            if (! $this->isAllowedOptionValue($options, $itemStr)) {
                return 'The selected value is invalid.';
            }
        }

        return null;
    }

    private function isAllowedOptionValue(array $options, string $value): bool
    {
        $locales = config('dynamic-forms.locales', ['en']);
        foreach ($locales as $locale) {
            foreach ($options[$locale] ?? [] as $option) {
                $optionValue = trim((string) ($option['value'] ?? $option['label'] ?? ''));
                $optionLabel = trim((string) ($option['label'] ?? $option['value'] ?? ''));

                if ($optionValue === $value || $optionLabel === $value) {
                    return true;
                }
            }
        }

        return false;
    }
}

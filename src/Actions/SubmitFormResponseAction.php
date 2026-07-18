<?php

namespace MuhammadMahediHasan\Df\Actions;

use MuhammadMahediHasan\Df\Models\DynamicFormInput;
use MuhammadMahediHasan\Df\Models\FormResponse;
use MuhammadMahediHasan\Df\Models\FormResponseItem;
use MuhammadMahediHasan\Df\Support\BilingualFieldParser;
use MuhammadMahediHasan\Df\Support\FormInputOptionsParser;
use MuhammadMahediHasan\Df\Events\FormResponseSubmitted;
use Illuminate\Support\Facades\DB;

class SubmitFormResponseAction
{
    public function __construct(
        protected FormInputOptionsParser $optionsParser,
        protected BilingualFieldParser $bilingualParser
    ) {}

    /**
     * Store submission data for a form, capturing snaps of values.
     */
    public function execute(int $formId, $respondent, $subject, array $items, array $metadata = []): FormResponse
    {
        return DB::transaction(function () use ($formId, $respondent, $subject, $items, $metadata) {
            $response = FormResponse::create([
                'df_form_id' => $formId,
                'respondent_id' => $respondent?->getKey(),
                'respondent_type' => $respondent ? get_class($respondent) : null,
                'subject_id' => $subject?->getKey(),
                'subject_type' => $subject ? get_class($subject) : null,
                'date' => $metadata['date'] ?? now()->toDateString(),
                'lat' => $metadata['lat'] ?? null,
                'lon' => $metadata['lon'] ?? null,
                'meta_data' => $metadata['meta_data'] ?? null,
                'status' => $metadata['status'] ?? 'Pending',
            ]);

            $formInputs = DynamicFormInput::where('df_form_id', $formId)
                ->with('input')
                ->get()
                ->keyBy('id');

            $insertRows = [];

            foreach ($items as $item) {
                $inputId = $item['dynamicFormInputId'] ?? null;
                $formInput = $formInputs->get($inputId);

                if (! $formInput) {
                    continue;
                }

                $component = $formInput->input?->component ?? 'InputText';
                $options = $this->optionsParser->parseFromDynamicFormInput($formInput);

                if (in_array($component, config('dynamic-forms.value_skipped_components', ['Group', 'Header']), true)) {
                    $value = null;
                } else {
                    $value = $this->normalizeValue($item['value'] ?? null);
                }

                $hasOptions = false;
                if (is_array($options)) {
                    foreach ($options as $locale => $opts) {
                        if (!empty($opts)) {
                            $hasOptions = true;
                            break;
                        }
                    }
                }
                $storedOptions = $hasOptions ? $options : null;

                $insertRows[] = [
                    'df_response_id' => $response->id,
                    'df_field_id' => $formInput->id,
                    'parent_id' => $formInput->parent_id,
                    'label' => json_encode($this->bilingualParser->parse($formInput->label)),
                    'component' => $component,
                    'options' => $storedOptions ? json_encode($storedOptions) : null,
                    'value' => $value !== null ? json_encode($value) : null,
                    'is_visible' => (bool) ($item['isVisible'] ?? true),
                    'manual_mark' => isset($item['manual_mark']) ? (int) $item['manual_mark'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($insertRows)) {
                FormResponseItem::insert($insertRows);
            }

            event(new FormResponseSubmitted($response));

            return $response;
        });
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $normalized = array_map(function ($val) {
                return is_string($val) ? trim($val) : $val;
            }, $value);
            return empty($normalized) ? null : $normalized;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }

        return $value;
    }
}

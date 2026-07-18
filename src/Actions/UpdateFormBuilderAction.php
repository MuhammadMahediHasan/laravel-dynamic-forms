<?php

namespace MuhammadMahediHasan\Df\Actions;

use MuhammadMahediHasan\Df\Models\DynamicForm;
use MuhammadMahediHasan\Df\Models\DynamicFormInput;
use MuhammadMahediHasan\Df\Support\FormInputOptionsStorageNormalizer;
use Illuminate\Support\Facades\DB;

use MuhammadMahediHasan\Df\Actions\Concerns\RecalculatesSortOrder;

class UpdateFormBuilderAction
{
    use RecalculatesSortOrder;

    public function __construct(
        protected FormInputOptionsStorageNormalizer $optionsNormalizer
    ) {}

    /**
     * Update an existing DynamicForm and its fields.
     */
    public function execute(DynamicForm $form, array $data, array $elements): DynamicForm
    {
        return DB::transaction(function () use ($form, $data, $elements) {
            $updateFields = [];
            foreach (['type', 'name', 'slug', 'description', 'status', 'end_at', 'is_public'] as $key) {
                if (array_key_exists($key, $data)) {
                    $updateFields[$key] = $data[$key];
                }
            }
            if (! empty($updateFields)) {
                $form->update($updateFields);
            }

            $processedIds = [];
            $idMap = [];
            $created = [];

            foreach ($elements as $i => $item) {
                $row = null;

                if (! empty($item['id'])) {
                    $row = DynamicFormInput::where('id', $item['id'])
                        ->where('df_form_id', $form->id)
                        ->first();

                    if ($row) {
                        $row->update($this->buildFieldPayload($item, $i + 1));
                        $processedIds[] = $row->id;
                    }
                }

                if (! $row) {
                    $payload = $this->buildFieldPayload($item, $i + 1);
                    $payload['df_form_id'] = $form->id;
                    $payload['df_form_input_id'] = $item['form_input_id'];
                    $payload['parent_id'] = $item['parent_id'] ?? null;

                    $row = DynamicFormInput::create($payload);
                    $processedIds[] = $row->id;
                }

                $key = $item['temp_key'] ?? $item['id'] ?? null;
                if ($key) {
                    $idMap[$key] = $row->id;
                }
                $created[] = [$row, $item];
            }

            // Remove form inputs that are no longer part of the form builder layout
            DynamicFormInput::where('df_form_id', $form->id)
                ->whereNotIn('id', $processedIds)
                ->delete();

            // Set parent/condition references
            foreach ($created as [$row, $item]) {
                $updates = [];
                $parentKey = $item['parent_key'] ?? null;
                if (! empty($parentKey) && isset($idMap[$parentKey])) {
                    $updates['parent_id'] = $idMap[$parentKey];
                }
                $conditionKey = $item['condition_input_id'] ?? null;
                if (! empty($conditionKey) && isset($idMap[$conditionKey])) {
                    $updates['condition_input_id'] = $idMap[$conditionKey];
                }
                if (! empty($updates)) {
                    $row->update($updates);
                }
            }

            $this->recalculateSerialSort($form->id);

            return $form;
        });
    }

    /**
     * Build common field attributes array.
     */
    protected function buildFieldPayload(array $item, int $sort): array
    {
        return [
            'label' => $item['label'] ?? null,
            'placeholder' => $item['placeholder'] ?? null,
            'hints' => $item['hints'] ?? null,
            'icon' => $item['icon'] ?? null,
            'options' => $this->optionsNormalizer->normalize(
                $item['options'] ?? null,
                (string) ($item['type'] ?? ''),
            ),
            'correct_answer' => $item['correct_answer'] ?? null,
            'marks' => (int) ($item['marks'] ?? 1),
            'required' => (bool) ($item['required'] ?? false),
            'sort' => $sort,
            'condition_value' => $item['condition_value'] ?? null,
            'is_repeatable' => (bool) ($item['is_repeatable'] ?? false),
            'repeat_min' => (int) ($item['repeat_min'] ?? 1),
            'repeat_max' => isset($item['repeat_max']) ? (int) $item['repeat_max'] : null,
        ];
    }
}

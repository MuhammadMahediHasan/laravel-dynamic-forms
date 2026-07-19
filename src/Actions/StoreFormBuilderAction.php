<?php

namespace MuhammadMahediHasan\Df\Actions;

use MuhammadMahediHasan\Df\Enums\FormStatus;
use MuhammadMahediHasan\Df\Models\DynamicForm;
use MuhammadMahediHasan\Df\Models\DynamicFormInput;
use MuhammadMahediHasan\Df\Support\FormInputOptionsStorageNormalizer;
use Illuminate\Support\Facades\DB;

use MuhammadMahediHasan\Df\Actions\Concerns\RecalculatesSortOrder;

class StoreFormBuilderAction
{
    use RecalculatesSortOrder;

    public function __construct(
        protected FormInputOptionsStorageNormalizer $optionsNormalizer
    ) {}

    /**
     * Create a new DynamicForm and nested fields.
     */
    public function execute(array $data, array $elements): DynamicForm
    {
        return DB::transaction(function () use ($data, $elements) {
            $form = DynamicForm::create([
                'type' => $data['type'] ?? 'Survey',
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? FormStatus::PENDING,
            ]);

            $idMap = [];
            $created = [];

            foreach ($elements as $i => $item) {
                $row = DynamicFormInput::create([
                    'df_form_id' => $form->id,
                    'df_form_input_id' => $item['form_input_id'], // Base FormInput ID
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
                    'sort' => $i + 1,
                    'condition_input_id' => null,
                    'condition_value' => $item['condition_value'] ?? null,
                    'is_repeatable' => (bool) ($item['is_repeatable'] ?? false),
                    'repeat_min' => (int) ($item['repeat_min'] ?? 1),
                    'repeat_max' => isset($item['repeat_max']) ? (int) $item['repeat_max'] : null,
                    'parent_id' => null,
                ]);

                // Track the client-side/temporary ID to map hierarchy
                if (isset($item['id'])) {
                    $idMap[$item['id']] = $row->id;
                }
                $created[] = [$row, $item];
            }

            // Map parents and condition dependencies
            foreach ($created as [$row, $item]) {
                $updates = [];
                if (! empty($item['parent_key']) && isset($idMap[$item['parent_key']])) {
                    $updates['parent_id'] = $idMap[$item['parent_key']];
                }
                if (! empty($item['condition_input_id']) && isset($idMap[$item['condition_input_id']])) {
                    $updates['condition_input_id'] = $idMap[$item['condition_input_id']];
                }
                if (! empty($updates)) {
                    $row->update($updates);
                }
            }

            // Order serial sort recursively
            $this->recalculateSerialSort($form->id);

            return $form;
        });
    }
}

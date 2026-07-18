<?php

namespace MuhammadMahediHasan\Df\Actions\Concerns;

use MuhammadMahediHasan\Df\Models\DynamicFormInput;

trait RecalculatesSortOrder
{
    /**
     * Order serial sort recursively starting from top-level parents.
     */
    protected function recalculateSerialSort(int $formId): void
    {
        $inputs = DynamicFormInput::where('df_form_id', $formId)
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();

        $counter = 0;
        foreach ($inputs as $input) {
            $counter++;
            $input->update(['sort' => $counter]);
            $counter = $this->updateChildrenSort($input->id, $counter);
        }
    }

    /**
     * Recursively update children sort order.
     */
    protected function updateChildrenSort(int $parentId, int $counter): int
    {
        $children = DynamicFormInput::where('parent_id', $parentId)
            ->orderBy('sort')
            ->get();

        foreach ($children as $child) {
            $counter++;
            $child->update(['sort' => $counter]);
            $counter = $this->updateChildrenSort($child->id, $counter);
        }

        return $counter;
    }
}

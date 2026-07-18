<?php

namespace MuhammadMahediHasan\Df\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicFormInputResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_input_id' => $this->form_input_id,
            'type' => $this->input?->type,
            'component' => $this->input?->component,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'hints' => $this->hints,
            'icon' => $this->icon,
            'options' => $this->options,
            'required' => $this->required,
            'correct_answer' => $this->correct_answer,
            'marks' => $this->marks,
            'parent_id' => $this->parent_id,
            'condition_input_id' => $this->condition_input_id,
            'condition_value' => $this->condition_value,
            'is_repeatable' => $this->is_repeatable,
            'repeat_min' => $this->repeat_min,
            'repeat_max' => $this->repeat_max,
            'sort' => $this->sort,
        ];
    }
}

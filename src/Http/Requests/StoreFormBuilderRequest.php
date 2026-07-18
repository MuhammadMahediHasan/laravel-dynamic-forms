<?php

namespace MuhammadMahediHasan\Df\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MuhammadMahediHasan\Df\Enums\FormStatus;

class StoreFormBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => [
                'required',
                'string',
                'max:100',
                Rule::in(config('dynamic-forms.form_types', ['Survey', 'Quiz', 'Assessment', 'Monitoring'])),
            ],
            'slug' => 'required|string|max:255|unique:df_forms,slug',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::enum(FormStatus::class)],
            'end_at' => 'nullable|date',
            'is_public' => 'nullable|boolean',
            'elements' => 'required|array',
            'elements.*.form_input_id' => 'required|integer|exists:df_form_inputs,id',
            'elements.*.label' => 'required',
            'elements.*.placeholder' => 'nullable',
            'elements.*.hints' => 'nullable',
            'elements.*.icon' => 'nullable|string',
            'elements.*.options' => 'nullable',
            'elements.*.correct_answer' => 'nullable',
            'elements.*.marks' => 'nullable|integer',
            'elements.*.required' => 'nullable|boolean',
            'elements.*.is_repeatable' => 'nullable|boolean',
            'elements.*.repeat_min' => 'nullable|integer',
            'elements.*.repeat_max' => 'nullable|integer',
        ];
    }
}

<?php

namespace MuhammadMahediHasan\Df\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MuhammadMahediHasan\Df\Enums\FormStatus;

class UpdateFormBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formId = $this->route('form')?->id;

        return [
            'name' => 'nullable|string|max:255',
            'type' => [
                'nullable',
                'string',
                'max:100',
                Rule::in(config('dynamic-forms.form_types', ['Survey', 'Quiz', 'Assessment', 'Monitoring'])),
            ],
            'slug' => 'nullable|string|max:255|unique:df_forms,slug,' . $formId,
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::enum(FormStatus::class)],
            'end_at' => 'nullable|date',
            'is_public' => 'nullable|boolean',
            'elements' => 'required|array',
            'elements.*.id' => 'nullable|integer|exists:df_fields,id',
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

<?php

namespace MuhammadMahediHasan\Df\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'respondent_id' => 'nullable',
            'respondent_type' => [
                'nullable',
                'string',
                Rule::in(config('dynamic-forms.allowed_respondent_types', ['App\Models\User'])),
            ],
            'subject_id' => 'nullable',
            'subject_type' => [
                'nullable',
                'string',
                Rule::in(config('dynamic-forms.allowed_subject_types', ['App\Models\User'])),
            ],
            'date' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lon' => 'nullable|numeric',
            'meta_data' => 'nullable|array',
            'items' => 'required|array',
            'items.*.dynamicFormInputId' => 'required|integer',
            'items.*.value' => 'nullable',
            'items.*.bnValue' => 'nullable',
            'items.*.options' => 'nullable',
            'items.*.isVisible' => 'nullable|boolean',
            'items.*.manual_mark' => 'nullable|integer',
        ];
    }
}

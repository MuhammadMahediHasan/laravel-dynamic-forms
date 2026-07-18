<?php

namespace MuhammadMahediHasan\Df\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DynamicFormInput extends Model
{
    use SoftDeletes;

    protected $table = 'df_fields';

    protected $fillable = [
        'df_form_id',
        'df_form_input_id',
        'parent_id',
        'label',
        'placeholder',
        'hints',
        'icon',
        'options',
        'required',
        'correct_answer',
        'marks',
        'condition_input_id',
        'condition_value',
        'is_repeatable',
        'repeat_min',
        'repeat_max',
        'sort',
    ];

    protected $casts = [
        'label' => 'array',
        'placeholder' => 'array',
        'hints' => 'array',
        'options' => 'array',
        'correct_answer' => 'array',
        'required' => 'boolean',
        'is_repeatable' => 'boolean',
        'repeat_min' => 'integer',
        'repeat_max' => 'integer',
        'sort' => 'integer',
        'marks' => 'integer',
        'condition_value' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(DynamicForm::class, 'df_form_id');
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(FormInput::class, 'df_form_input_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function conditionInput(): BelongsTo
    {
        return $this->belongsTo(self::class, 'condition_input_id');
    }
}

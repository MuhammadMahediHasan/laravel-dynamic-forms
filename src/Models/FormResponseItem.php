<?php

namespace MuhammadMahediHasan\Df\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormResponseItem extends Model
{
    protected $table = 'df_response_items';

    protected $fillable = [
        'df_response_id',
        'df_field_id',
        'parent_id',
        'label',
        'component',
        'options',
        'value',
        'is_visible',
        'manual_mark',
    ];

    protected $casts = [
        'label' => 'array',
        'options' => 'array',
        'value' => 'array',
        'is_visible' => 'boolean',
        'manual_mark' => 'integer',
    ];

    public function formResponse(): BelongsTo
    {
        return $this->belongsTo(FormResponse::class, 'df_response_id');
    }

    public function formInput(): BelongsTo
    {
        return $this->belongsTo(DynamicFormInput::class, 'df_field_id');
    }

    public function parentInput(): BelongsTo
    {
        return $this->belongsTo(DynamicFormInput::class, 'parent_id');
    }
}

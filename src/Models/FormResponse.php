<?php

namespace MuhammadMahediHasan\Df\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FormResponse extends Model
{
    use SoftDeletes;

    protected $table = 'df_responses';

    protected $fillable = [
        'df_form_id',
        'respondent_id',
        'respondent_type',
        'subject_id',
        'subject_type',
        'date',
        'lat',
        'lon',
        'meta_data',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'lat' => 'decimal:8',
        'lon' => 'decimal:8',
        'meta_data' => 'array',
    ];

    public function dynamicForm(): BelongsTo
    {
        return $this->belongsTo(DynamicForm::class, 'df_form_id');
    }

    public function respondent(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(FormResponseItem::class, 'df_response_id');
    }
}

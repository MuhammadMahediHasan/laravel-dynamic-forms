<?php

namespace MuhammadMahediHasan\Df\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

use MuhammadMahediHasan\Df\Enums\FormStatus;

class DynamicForm extends Model
{
    use SoftDeletes;

    protected $table = 'df_forms';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'status',
        'end_at',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'end_at' => 'datetime',
        'status' => FormStatus::class,
    ];

    public function scopePublished($query)
    {
        return $query->where('status', FormStatus::ACTIVE)->where('is_public', true);
    }

    /**
     * Get all inputs defined for this form.
     */
    public function inputs(): HasMany
    {
        return $this->hasMany(DynamicFormInput::class, 'df_form_id')->orderBy('sort');
    }

    /**
     * Get all submissions/responses collected for this form.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(FormResponse::class, 'df_form_id');
    }
}

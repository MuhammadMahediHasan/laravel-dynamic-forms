<?php

namespace MuhammadMahediHasan\Df\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormInput extends Model
{
    protected $table = 'df_form_inputs';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'component',
        'icon',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function dynamicFormInputs(): HasMany
    {
        return $this->hasMany(DynamicFormInput::class, 'df_form_input_id');
    }
}

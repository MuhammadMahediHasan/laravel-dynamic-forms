<?php

namespace MuhammadMahediHasan\Df\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
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
    ];

    protected $casts = [
        'status' => FormStatus::class,
    ];

    public function scopePublished($query)
    {
        return $query->where('status', FormStatus::ACTIVE);
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

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name);
            } else {
                $form->slug = Str::slug($form->slug);
            }

            // Ensure uniqueness
            $originalSlug = $form->slug;
            $count = 1;
            while (static::where('slug', $form->slug)->exists()) {
                $form->slug = "{$originalSlug}-{$count}";
                $count++;
            }
        });

        static::updating(function ($form) {
            if ($form->isDirty('name') && !$form->isDirty('slug')) {
                $form->slug = Str::slug($form->name);
            } elseif ($form->isDirty('slug')) {
                $form->slug = Str::slug($form->slug);
            }

            // Ensure uniqueness, ignoring current record
            if ($form->isDirty('slug')) {
                $originalSlug = $form->slug;
                $count = 1;
                while (static::where('slug', $form->slug)->where('id', '!=', $form->id)->exists()) {
                    $form->slug = "{$originalSlug}-{$count}";
                    $count++;
                }
            }
        });
    }

    /**
     * Retrieve the model for a bound value (supports both ID and slug).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->firstOrFail();
        }

        if (is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        return $this->where('slug', $value)->firstOrFail();
    }
}

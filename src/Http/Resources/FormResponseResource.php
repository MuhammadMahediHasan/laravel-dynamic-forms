<?php

namespace MuhammadMahediHasan\Df\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'df_form_id' => $this->df_form_id,
            'respondent_id' => $this->respondent_id,
            'respondent_type' => $this->respondent_type,
            'subject_id' => $this->subject_id,
            'subject_type' => $this->subject_type,
            'date' => $this->date?->toDateString(),
            'lat' => $this->lat,
            'lon' => $this->lon,
            'meta_data' => $this->meta_data,
            'status' => $this->status,
            'items' => $this->relationLoaded('items') ? $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'df_field_id' => $item->df_field_id,
                    'parent_id' => $item->parent_id,
                    'label' => $item->label,
                    'component' => $item->component,
                    'options' => $item->options,
                    'value' => $item->value,
                    'is_visible' => $item->is_visible,
                    'manual_mark' => $item->manual_mark,
                ];
            }) : [],
        ];
    }
}

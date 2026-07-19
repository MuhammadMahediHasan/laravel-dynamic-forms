<?php

namespace MuhammadMahediHasan\Df\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'elements' => $this->relationLoaded('inputs')
                ? DynamicFormInputResource::collection($this->inputs)
                : [],
        ];
    }
}

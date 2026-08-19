<?php

namespace App\Http\Resources\SubServiceModel;

 use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceModelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'title' => $this->title,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'sections' => SubServiceModelSectionResource::collection($this->sections),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

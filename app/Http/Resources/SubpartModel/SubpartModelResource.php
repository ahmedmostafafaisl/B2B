<?php

namespace App\Http\Resources\SubpartModel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubpartModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_part_id' => $this->sub_part_id,
            'title' => $this->title,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'sections' => SubpartModelSectionResource::collection($this->whenLoaded('sections')),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

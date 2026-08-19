<?php

namespace App\Http\Resources\SubServiceFeature;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceFeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'title' => $this->title,
            // 'image_path' => $this->image,
            'image' => $this->image_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'types' => SubServiceFeatureTypeResource::collection($this->types),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

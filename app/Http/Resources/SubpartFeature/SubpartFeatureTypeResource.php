<?php

namespace App\Http\Resources\SubpartFeature;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubpartFeatureTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sort_order' => $this->sort_order,
            'items' => SubpartFeatureItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

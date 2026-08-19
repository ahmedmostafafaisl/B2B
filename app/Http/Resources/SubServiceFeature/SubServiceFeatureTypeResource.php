<?php

namespace App\Http\Resources\SubServiceFeature;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceFeatureTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sort_order' => $this->sort_order,
            'items' => SubServiceFeatureItemResource::collection($this->items),
        ];
    }
}

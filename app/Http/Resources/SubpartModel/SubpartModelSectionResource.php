<?php

namespace App\Http\Resources\SubpartModel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubpartModelSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sort_order' => $this->sort_order,
            'items' => SubpartModelItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

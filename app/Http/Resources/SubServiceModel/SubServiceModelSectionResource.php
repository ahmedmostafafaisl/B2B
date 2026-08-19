<?php

namespace App\Http\Resources\SubServiceModel;

 use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceModelSectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sort_order' => $this->sort_order,
            'items' => SubServiceModelItemResource::collection($this->items),
        ];
    }
}


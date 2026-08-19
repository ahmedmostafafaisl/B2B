<?php

namespace App\Http\Resources\SubServiceModel;

 use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceModelItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'sort_order' => $this->sort_order,
        ];
    }
}

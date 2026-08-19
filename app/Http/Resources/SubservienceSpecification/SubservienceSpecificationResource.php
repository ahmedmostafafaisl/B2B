<?php

namespace App\Http\Resources\SubservienceSpecification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubservienceSpecificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

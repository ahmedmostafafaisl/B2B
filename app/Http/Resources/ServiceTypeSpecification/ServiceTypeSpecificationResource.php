<?php

namespace App\Http\Resources\ServiceTypeSpecification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceTypeSpecificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'service_type_id' => $this->service_type_id,
            'service_type' => $this->whenLoaded('serviceType', fn () => [
                'id' => $this->serviceType->id,
                'name' => $this->serviceType->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}

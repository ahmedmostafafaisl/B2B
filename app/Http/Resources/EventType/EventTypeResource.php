<?php

namespace App\Http\Resources\EventType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'status' => $this->status,
            'requires_approval' => $this->requires_approval,
            'affects_availability' => $this->affects_availability,
            'color' => $this->color,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

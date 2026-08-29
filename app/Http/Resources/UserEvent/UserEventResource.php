<?php

namespace App\Http\Resources\UserEvent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->username,
            ] : null),

            'event_type' => $this->whenLoaded('eventType', fn() => $this->eventType ? [
                'id'    => $this->eventType->id,
                'name'  => $this->eventType->name,
                'label' => $this->eventType->label,
                'color' => $this->eventType->color,
            ] : null),

            'status' => $this->status,
            'note' => $this->note,

            'start_at' => optional($this->start_at)->toDateString(),
            'end_at'   => optional($this->end_at)->toDateString(),

            'days_of_week' => $this->days_of_week,
            'start_time'   => $this->start_time,
            'end_time'     => $this->end_time,

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

<?php

namespace App\Http\Resources\ActivityLog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'loggable_type' => $this->loggable_type,
            'loggable_id'   => $this->loggable_id,
            'user_id'       => $this->user_id,
            'username'      => $this->whenLoaded('user', fn() => $this->user?->username),
            'action'        => $this->action,
            'note'          => $this->note,
            'old_values'    => $this->old_values,
            'new_values'    => $this->new_values,
            'meta'          => $this->meta,
            'created_at'    => optional($this->created_at)->toISOString(),
        ];
    }
}

<?php

namespace App\Http\Resources\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'subject_id' => $this->subject_id,
            'key_id' => $this->key_id,

            'subject' => $this->whenLoaded('subject', fn() => [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ]),

            'key' => $this->whenLoaded('key', fn() => [
                'id' => $this->key->id,
                'name' => $this->key->name,
                'key' => $this->key->key,
            ]),

            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'status' => $this->status,
            'offer_price' => $this->offer_price,
            'completed_at' => optional($this->completed_at)->toISOString(),
            'note' => $this->note,

            'logs' => $this->whenLoaded('activityLogs', fn() => $this->activityLogs->map(function ($log) {
                return [
                    'id'            => $log->id,
                    'user_id'       => $log->user_id,
                    'username'      => $log->user?->username,
                    'action'        => $log->action,
                    'note'          => $log->note,
                    'sent_to_email' => $log->meta['sent_to_email'] ?? null,
                    'old_values'    => $log->old_values,
                    'new_values'    => $log->new_values,
                    'created_at'    => $log->created_at,
                ];
            })),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

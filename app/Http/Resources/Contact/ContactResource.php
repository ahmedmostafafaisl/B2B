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

            'source' => $this->source,
            'utm_source' => $this->utm_source,
            'utm_campaign' => $this->utm_campaign,
            'prod_category' => $this->prod_category,
            'utm_medium' => $this->utm_medium,
            'source_page' => $this->source_page,

            'status' => $this->status,
            'offer_price' => $this->offer_price,
            'completed_at' => optional($this->completed_at)->toISOString(),
            'note' => $this->note,

            'has_task' => (bool) ($this->tasks_exists ?? false),

            'created_by' => $this->whenLoaded('latestTask', fn() => $this->latestTask?->createdBy ? [
                'id'   => $this->latestTask->createdBy->id,
                'name' => $this->latestTask->createdBy->username,
            ] : null),

            'assigned_to' => $this->whenLoaded('latestTask', fn() => $this->latestTask?->assignedTo ? [
                'id'   => $this->latestTask->assignedTo->id,
                'name' => $this->latestTask->assignedTo->username,
            ] : null),

            'closing_reasons' => $this->whenLoaded('closingReasons', fn() => $this->closingReasons->map(fn($reason) => [
                'id'    => $reason->id,
                'name'  => $reason->name,
                'label' => $reason->label,
            ])),

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

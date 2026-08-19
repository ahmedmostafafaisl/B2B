<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\Contact\ContactResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'due_date'    => optional($this->due_date)->toDateString(),

            // Full contact record, reusing ContactResource so this stays in
            // sync with the Contact module automatically.
            'contact' => $this->contact ? new ContactResource($this->contact) : null,

            'assigned_to' => $this->assignedTo ? [
                'id'   => $this->assignedTo->id,
                'name' => $this->assignedTo->username,
            ] : null,

            'created_by' => $this->createdBy ? [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->username,
            ] : null,

            'comments' => $this->whenLoaded('comments', fn() => $this->comments->map(fn($comment) => [
                'id'   => $comment->id,
                'user' => $comment->creator ? [
                    'id'   => $comment->creator->id,
                    'name' => $comment->creator->username,
                ] : null,
                'body'       => $comment->body,
                'created_at' => optional($comment->created_at)->toISOString(),
            ])),

            'logs' => $this->whenLoaded('activityLogs', fn() => $this->activityLogs->map(fn($log) => [
                'id'         => $log->id,
                'user_id'    => $log->user_id,
                'username'   => $log->user?->username,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'created_at' => optional($log->created_at)->toISOString(),
            ])),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

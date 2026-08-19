<?php

namespace App\Http\Resources\Task;

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

            'contact_id' => $this->contact_id,
            'contact' => $this->whenLoaded('contact', fn() => $this->contact ? [
                'id'   => $this->contact->id,
                'name' => $this->contact->name,
            ] : null),

            'assigned_to' => $this->assigned_to,
            'assigned_to_user' => $this->whenLoaded('assignedTo', fn() => $this->assignedTo ? [
                'id'       => $this->assignedTo->id,
                'username' => $this->assignedTo->username,
            ] : null),

            'created_by' => $this->created_by,
            'created_by_user' => $this->whenLoaded('createdBy', fn() => $this->createdBy ? [
                'id'       => $this->createdBy->id,
                'username' => $this->createdBy->username,
            ] : null),

            'comments' => $this->whenLoaded('comments', fn() => $this->comments->map(fn($comment) => [
                'id'                 => $comment->id,
                'comment_created_by' => $comment->comment_created_by,
                'username'           => $comment->creator?->username,
                'body'               => $comment->body,
                'created_at'         => optional($comment->created_at)->toISOString(),
            ])),

            'logs' => $this->whenLoaded('activityLogs', fn() => $this->activityLogs->map(fn($log) => [
                'id'         => $log->id,
                'user_id'    => $log->user_id,
                'username'   => $log->user?->username,
                'action'     => $log->action,
                'note'       => $log->note,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'created_at' => optional($log->created_at)->toISOString(),
            ])),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

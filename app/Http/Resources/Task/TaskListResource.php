<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskListResource extends JsonResource
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

            'contact' => $this->contact ? [
                'id'    => $this->contact->id,
                'name'  => $this->contact->name,
                'email' => $this->contact->email,
            ] : null,

            'assigned_to' => $this->assignedTo ? [
                'id'   => $this->assignedTo->id,
                'name' => $this->assignedTo->username,
            ] : null,

            'created_by' => $this->createdBy ? [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->username,
            ] : null,

            'comments_count' => $this->comments_count ?? $this->comments()->count(),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

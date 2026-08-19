<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'task_id'            => $this->task_id,
            'comment_created_by' => $this->comment_created_by,
            'username'           => $this->whenLoaded('creator', fn() => $this->creator?->username),
            'body'               => $this->body,
            'created_at'         => optional($this->created_at)->toISOString(),
        ];
    }
}

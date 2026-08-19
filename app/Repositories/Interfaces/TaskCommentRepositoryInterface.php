<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

interface TaskCommentRepositoryInterface
{
    public function indexForTask(Task $task, Request $request): array;

    public function store(Task $task, array $data): TaskComment;

    public function delete(TaskComment $comment): void;
}

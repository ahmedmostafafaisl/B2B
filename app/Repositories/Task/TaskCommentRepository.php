<?php

namespace App\Repositories\Task;

use App\Models\Task;
use App\Models\TaskComment;
use App\Repositories\Interfaces\TaskCommentRepositoryInterface;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TaskCommentRepository implements TaskCommentRepositoryInterface
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function indexForTask(Task $task, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $task->comments()
            ->with('creator')
            ->paginate($perPage, ['*'], 'page', $currentPage);

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ];
    }

    public function store(Task $task, array $data): TaskComment
    {
        $comment = $task->comments()->create([
            'comment_created_by' => Auth::id(),
            'body' => $data['body'],
        ]);

        // The comment also shows up on the task's own audit trail.
        $this->activityLogService->record(
            model: $task,
            action: 'comment_added',
            note: $data['body'],
            meta: ['comment_id' => $comment->id],
        );

        return $comment->load('creator');
    }

    public function delete(TaskComment $comment): void
    {
        $task = $comment->task;

        $this->activityLogService->record(
            model: $task,
            action: 'comment_deleted',
            note: $comment->body,
            meta: ['comment_id' => $comment->id],
        );

        $comment->delete();
    }
}

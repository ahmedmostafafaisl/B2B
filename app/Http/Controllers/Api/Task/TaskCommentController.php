<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskCommentStoreRequest;
use App\Http\Resources\Task\TaskCommentResource;
use App\Models\Task;
use App\Models\TaskComment;
use App\Repositories\Interfaces\TaskCommentRepositoryInterface;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function __construct(private readonly TaskCommentRepositoryInterface $comments)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request, Task $task)
    {
        $result = $this->comments->indexForTask($task, $request);

        return response()->json([
            'items' => TaskCommentResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(TaskCommentStoreRequest $request, Task $task)
    {
        $comment = $this->comments->store($task, $request->validated());

        return (new TaskCommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(TaskComment $comment)
    {
        $this->comments->delete($comment);

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}

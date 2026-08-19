<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Http\Resources\Task\TaskListResource;
use App\Http\Resources\Task\TaskResource;
use App\Http\Resources\Task\UserBriefResource;
use App\Http\Resources\Task\UserTaskSummaryResource;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepositoryInterface $tasks)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $result = $this->tasks->index($request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'items' => TaskListResource::collection($result['items']),
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    public function store(TaskStoreRequest $request)
    {
        $task = $this->tasks->store($request->validated());
        $task->load(['contact', 'assignedTo', 'createdBy']);

        return response()->json([
            'status' => true,
            'data' => new TaskResource($task),
        ], 201);
    }

    public function show(int $id)
    {
        $task = $this->tasks->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new TaskResource($task),
        ]);
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $updated = $this->tasks->update($task, $request->validated());
        $updated->load(['contact', 'assignedTo', 'createdBy', 'comments', 'comments.creator', 'activityLogs', 'activityLogs.user']);

        return response()->json([
            'status' => true,
            'data' => new TaskResource($updated),
        ]);
    }

    public function destroy(int $id)
    {
        $task = $this->tasks->findOrFail($id);
        $this->tasks->delete($task);

        return response()->json([
            'status' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * GET /tasks/user/{userId} — tasks assigned to a specific user.
     */
    public function userTasks(Request $request, int $userId)
    {
        $result = $this->tasks->forUser($userId, $request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'items' => TaskListResource::collection($result['items']),
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    /**
     * GET /tasks/assignees — users that currently have at least one task
     * assigned to them, with a count per user.
     */
    public function usersWithTasks(Request $request)
    {
        $result = $this->tasks->usersWithTasks($request);

        return response()->json([
            'status' => true,
            'data' => [
                'items' => UserTaskSummaryResource::collection($result['items']),
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    /**
     * GET /tasks/assignees/{userId} — a single user plus their assigned
     * tasks (paginated).
     */
    public function singleUserWithTasks(Request $request, int $userId)
    {
        $result = $this->tasks->singleUserWithTasks($userId, $request);

        return response()->json([
            'status' => true,
            'data' => [
                'user' => new UserBriefResource($result['user']),
                'tasks' => [
                    'items' => TaskListResource::collection($result['tasks']['items']),
                    'pagination' => $result['tasks']['pagination'],
                ],
            ],
        ]);
    }
}

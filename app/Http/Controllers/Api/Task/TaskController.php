<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
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

        // index() returns a JsonResponse directly on validation failure.
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        return response()->json([
            'items' => TaskResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(TaskStoreRequest $request)
    {
        $task = $this->tasks->store($request->validated());

        return (new TaskResource($task->load(['contact', 'assignedTo', 'createdBy'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $task = $this->tasks->findOrFail($id);

        return new TaskResource($task);
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        $updated = $this->tasks->update($task, $request->validated());

        return new TaskResource($updated->load(['contact', 'assignedTo', 'createdBy']));
    }

    public function destroy(int $id)
    {
        $task = $this->tasks->findOrFail($id);
        $this->tasks->delete($task);

        return response()->json(['message' => 'Task deleted successfully']);
    }
}

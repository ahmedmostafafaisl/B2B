<?php

namespace App\Repositories\Task;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskRepository implements TaskRepositoryInterface
{
    public const VALID_STATUSES = [
        'new',
        'in_progress',
        'review',
        'completed',
        'cancelled',
    ];

    public const VALID_PRIORITIES = [
        'low',
        'medium',
        'high',
        'urgent',
    ];

    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): JsonResponse|array
    {
        if ($request->filled('status') && ! in_array($request->input('status'), self::VALID_STATUSES, true)) {
            return response()->json([
                'message' => 'Invalid status value.',
                'errors' => [
                    'status' => ['Allowed values: ' . implode(', ', self::VALID_STATUSES)],
                ],
            ], 422);
        }

        if ($request->filled('priority') && ! in_array($request->input('priority'), self::VALID_PRIORITIES, true)) {
            return response()->json([
                'message' => 'Invalid priority value.',
                'errors' => [
                    'priority' => ['Allowed values: ' . implode(', ', self::VALID_PRIORITIES)],
                ],
            ], 422);
        }

        $query = Task::query()
            ->with(['contact', 'assignedTo', 'createdBy'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn($q) => $q->where('priority', $request->input('priority')))
            ->when($request->filled('assigned_to'), fn($q) => $q->where('assigned_to', $request->integer('assigned_to')))
            ->when($request->filled('contact_id'), fn($q) => $q->where('contact_id', $request->integer('contact_id')))
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Task
    {
        return Task::query()
            ->with(['contact', 'assignedTo', 'createdBy', 'comments', 'comments.creator', 'activityLogs', 'activityLogs.user'])
            ->findOrFail($id);
    }

    public function store(array $data): Task
    {
        $data['created_by'] = Auth::id();

        $task = Task::create($data);

        $this->activityLogService->record(
            model: $task,
            action: 'create',
            newValues: $task->only([
                'title',
                'description',
                'status',
                'priority',
                'due_date',
                'contact_id',
                'assigned_to',
                'created_by',
            ]),
        );

        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->fill($data);
        $changes = $task->getDirty();

        if (!empty($changes)) {
            $oldValues = [];
            foreach ($changes as $field => $value) {
                $oldValues[$field] = $task->getOriginal($field);
            }

            $task->save();

            $this->activityLogService->recordChanges(
                model: $task,
                oldValues: $oldValues,
                newValues: $changes,
                meta: [
                    'status_changed'   => isset($changes['status']),
                    'old_status'       => $oldValues['status'] ?? null,
                    'new_status'       => $task->status,
                    'reassigned'       => isset($changes['assigned_to']),
                    'old_assigned_to'  => $oldValues['assigned_to'] ?? null,
                    'new_assigned_to'  => $task->assigned_to,
                ],
            );
        }

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    private function paginate($query, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

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
}

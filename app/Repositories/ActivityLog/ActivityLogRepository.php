<?php

namespace App\Repositories\ActivityLog;

use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Task;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Http\Request;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    /**
     * Whitelisted short-key => model class map. Add an entry here once a
     * model uses the HasActivityLogs trait and should be readable through
     * the generic /activity-logs endpoint.
     */
    public const LOGGABLE_TYPES = [
        'contact' => Contact::class,
        'task'    => Task::class,
    ];

    public function index(Request $request): array
    {
        $loggableClass = self::LOGGABLE_TYPES[$request->input('loggable_type')];

        $query = ActivityLog::query()
            ->with('user')
            ->where('loggable_type', $loggableClass)
            ->where('loggable_id', $request->integer('loggable_id'))
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->input('action')))
            ->orderByDesc('id');

        return $this->paginate($query, $request);
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

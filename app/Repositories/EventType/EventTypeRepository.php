<?php

namespace App\Repositories\EventType;

use App\Models\EventType;
use App\Repositories\Interfaces\EventTypeRepositoryInterface;
use Illuminate\Http\Request;

class EventTypeRepository implements EventTypeRepositoryInterface
{
    public const VALID_STATUSES = [
        'active',
        'inactive',
    ];

    public function index(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $query = EventType::query()
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id');

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

    public function findOrFail(int $id): EventType
    {
        return EventType::findOrFail($id);
    }

    public function store(array $data): EventType
    {
        return EventType::create($data);
    }

    public function update(EventType $eventType, array $data): EventType
    {
        $eventType->fill($data);
        $eventType->save();

        return $eventType->refresh();
    }

    public function delete(EventType $eventType): void
    {
        // user_events for this type cascade-delete at the DB level.
        $eventType->delete();
    }
}

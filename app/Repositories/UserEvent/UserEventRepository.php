<?php

namespace App\Repositories\UserEvent;

use App\Models\UserEvent;
use App\Repositories\Interfaces\UserEventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserEventRepository implements UserEventRepositoryInterface
{
    public const VALID_STATUSES = [
        'active',
        'inactive',
    ];

    public const WEEKDAYS = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];

    public function index(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $query = UserEvent::query()
            ->with(['user', 'eventType'])
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('event_type_id'), fn($q) => $q->where('event_type_id', $request->integer('event_type_id')))
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

    public function findOrFail(int $id): UserEvent
    {
        return UserEvent::with(['user', 'eventType'])->findOrFail($id);
    }

    public function store(array $data): Collection
    {
        $userIds = $data['user_ids'];

        $shared = [
            'event_type_id' => $data['event_type_id'],
            'status'        => $data['status'] ?? 'active',
            'note'          => $data['note'] ?? null,
            'start_at'      => $data['start_at'] ?? null,
            'end_at'        => $data['end_at'] ?? null,
            'days_of_week'  => $data['days_of_week'] ?? null,
            'start_time'    => $data['start_time'] ?? null,
            'end_time'      => $data['end_time'] ?? null,
        ];

        $createdIds = DB::transaction(function () use ($userIds, $shared) {
            $ids = [];
            foreach ($userIds as $userId) {
                $userEvent = UserEvent::create(array_merge($shared, ['user_id' => $userId]));
                $ids[] = $userEvent->id;
            }
            return $ids;
        });

        return UserEvent::with(['user', 'eventType'])->whereIn('id', $createdIds)->get();
    }

    public function update(UserEvent $userEvent, array $data): UserEvent
    {
        $userEvent->fill($data);
        $userEvent->save();

        return $userEvent->refresh()->load(['user', 'eventType']);
    }

    public function delete(UserEvent $userEvent): void
    {
        $userEvent->delete();
    }
}

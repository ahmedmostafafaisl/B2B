<?php

namespace App\Repositories\Contact;

use App\Models\Contact;
use App\Repositories\Interfaces\ContactRepositoryInterface;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactRepository implements ContactRepositoryInterface
{
    public const VALID_STATUSES = [
        'new',
        'in_progress',
        'contacted',
        'closed',
        'offer_price',
        'completed',
        'price_not_accepted',
        'not_serious',
        'needs_follow_up',
        'no_response',
        'awaiting_response',
        'unable_to_contact',
    ];

    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): JsonResponse|array
    {
        if ($request->has('status') && ! in_array($request->input('status'), self::VALID_STATUSES, true)) {
            return response()->json([
                'message' => 'Invalid status value.',
                'errors' => [
                    'status' => ['Allowed values: ' . implode(', ', self::VALID_STATUSES)],
                ],
            ], 422);
        }

        $query = Contact::query()
            ->with(['subject', 'key', 'activityLogs', 'activityLogs.user'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->integer('subject_id')))
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Contact
    {
        return Contact::query()
            ->with(['subject', 'key', 'activityLogs', 'activityLogs.user'])
            ->findOrFail($id);
    }

    public function store(array $data): Contact
    {
        $contact = Contact::create($data);

        $this->activityLogService->record(
            model: $contact,
            action: 'create',
            newValues: $contact->only([
                'subject_id',
                'key_id',
                'name',
                'email',
                'phone',
                'message',
                'status',
            ]),
        );

        return $contact;
    }

    public function update(Contact $contact, array $data): Contact
    {
        $note = $data['note'] ?? null;
        unset($data['note']);

        $contact->fill($data);
        $changes = $contact->getDirty();

        if (!empty($changes) || $note !== null) {

            // ── Old values — include all changed fields + note + status ──
            $oldValues = [];

            foreach ($changes as $field => $value) {
                $oldValues[$field] = $contact->getOriginal($field);
            }

            $oldValues['note']   = $contact->getOriginal('note')   ?? $contact->note;
            $oldValues['status'] = $contact->getOriginal('status') ?? $contact->status;

            // ── Save changed fields ───────────────────────────────────────
            if (!empty($changes)) {
                $contact->save();
            }

            // ── Save note separately ──────────────────────────────────────
            if ($note !== null) {
                $contact->note = $note;
                $contact->save();
            }

            // ── New values — all changes + note + current status ─────────
            $newValues = array_merge($changes, [
                'note'   => $note,
                'status' => $contact->status,
            ]);

            $this->activityLogService->recordChanges(
                model: $contact,
                oldValues: $oldValues,
                newValues: $newValues,
                note: $note,
                meta: [
                    'status_changed' => isset($changes['status']),
                    'old_status'     => $oldValues['status'] ?? null,
                    'new_status'     => $contact->status,
                ],
            );
        }

        return $contact->refresh();
    }

    public function delete(Contact $contact): void
    {
        $contact->delete();
    }

    // ✅ SAME pagination format you use everywhere
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

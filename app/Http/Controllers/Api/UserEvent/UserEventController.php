<?php

namespace App\Http\Controllers\Api\UserEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserEvent\UserEventStoreRequest;
use App\Http\Requests\UserEvent\UserEventUpdateRequest;
use App\Http\Resources\UserEvent\UserEventResource;
use App\Models\UserEvent;
use App\Repositories\Interfaces\UserEventRepositoryInterface;
use Illuminate\Http\Request;

class UserEventController extends Controller
{
    public function __construct(private readonly UserEventRepositoryInterface $userEvents)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $result = $this->userEvents->index($request);

        return response()->json([
            'status' => true,
            'data' => [
                'items' => UserEventResource::collection($result['items']),
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    /**
     * Bulk-creates one user_event row per id in user_ids, all sharing the
     * same event_type/status/note/schedule.
     */
    public function store(UserEventStoreRequest $request)
    {
        $created = $this->userEvents->store($request->validated());

        return response()->json([
            'status' => true,
            'data' => UserEventResource::collection($created),
        ], 201);
    }

    public function show(int $id)
    {
        $userEvent = $this->userEvents->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new UserEventResource($userEvent),
        ]);
    }

    public function update(UserEventUpdateRequest $request, UserEvent $userEvent)
    {
        $updated = $this->userEvents->update($userEvent, $request->validated());

        return response()->json([
            'status' => true,
            'data' => new UserEventResource($updated),
        ]);
    }

    public function destroy(int $id)
    {
        $userEvent = $this->userEvents->findOrFail($id);
        $this->userEvents->delete($userEvent);

        return response()->json([
            'status' => true,
            'message' => 'User event deleted successfully.',
        ]);
    }
}

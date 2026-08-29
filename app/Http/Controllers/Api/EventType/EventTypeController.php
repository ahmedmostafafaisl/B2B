<?php

namespace App\Http\Controllers\Api\EventType;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventType\EventTypeStoreRequest;
use App\Http\Requests\EventType\EventTypeUpdateRequest;
use App\Http\Resources\EventType\EventTypeResource;
use App\Models\EventType;
use App\Repositories\Interfaces\EventTypeRepositoryInterface;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    public function __construct(private readonly EventTypeRepositoryInterface $eventTypes)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $result = $this->eventTypes->index($request);

        return response()->json([
            'status' => true,
            'data' => [
                'items' => EventTypeResource::collection($result['items']),
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    public function store(EventTypeStoreRequest $request)
    {
        $eventType = $this->eventTypes->store($request->validated());

        return response()->json([
            'status' => true,
            'data' => new EventTypeResource($eventType),
        ], 201);
    }

    public function show(int $id)
    {
        $eventType = $this->eventTypes->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new EventTypeResource($eventType),
        ]);
    }

    public function update(EventTypeUpdateRequest $request, EventType $eventType)
    {
        $updated = $this->eventTypes->update($eventType, $request->validated());

        return response()->json([
            'status' => true,
            'data' => new EventTypeResource($updated),
        ]);
    }

    public function destroy(int $id)
    {
        $eventType = $this->eventTypes->findOrFail($id);
        $this->eventTypes->delete($eventType);

        return response()->json([
            'status' => true,
            'message' => 'Event type deleted successfully.',
        ]);
    }
}

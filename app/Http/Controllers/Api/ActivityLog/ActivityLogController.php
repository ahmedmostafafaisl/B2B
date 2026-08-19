<?php

namespace App\Http\Controllers\Api\ActivityLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityLog\ActivityLogIndexRequest;
use App\Http\Resources\ActivityLog\ActivityLogResource;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogRepositoryInterface $activityLogs,
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * GET /activity-logs?loggable_type=contact&loggable_id=5
     *
     * One reusable endpoint for any model that uses HasActivityLogs —
     * no need for a per-model logs controller.
     */
    public function index(ActivityLogIndexRequest $request)
    {
        $result = $this->activityLogs->index($request);

        return response()->json([
            'items' => ActivityLogResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }
}

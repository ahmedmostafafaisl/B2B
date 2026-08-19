<?php

namespace App\Http\Controllers\Api\GlobalSearch;

use App\Http\Controllers\Controller;
use App\Http\Requests\GlobalSearch\GlobalSearchRequest;
use App\Repositories\Interfaces\GlobalSearchRepositoryInterface;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    public function __construct(
        protected GlobalSearchRepositoryInterface $repository
    ) {}

    /**
     * GET /api/search?query=keyword
     */
    public function search(GlobalSearchRequest $request): JsonResponse
    {
        $query = $request->input('query');
        $grouped = $this->repository->search($query);

        // flat list of all results
        $flat = collect($grouped)
            ->flatMap(fn ($items) => $items)
            ->values();

        return response()->json([
            'success' => true,
            'query' => $query,
            'total' => $flat->count(),
            // 'results' => $flat,
            'grouped' => $grouped,
        ]);
    }
}

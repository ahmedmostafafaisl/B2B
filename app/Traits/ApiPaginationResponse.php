<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiPaginationResponse
{
    protected function paginatedResponse(LengthAwarePaginator $paginator, $resourceCollection)
    {
        return response()->json([
            'data' => $resourceCollection,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ]);
    }
}

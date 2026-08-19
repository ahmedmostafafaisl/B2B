<?php

namespace App\Http\Controllers\Api\SubservienceReview;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubservienceReview\StoreSubservienceReviewRequest;
use App\Http\Requests\SubservienceReview\UpdateSubservienceReviewRequest;
use App\Http\Resources\SubservienceReview\SubservienceReviewResource;
use App\Repositories\Interfaces\SubservienceReviewRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubservienceReviewController extends Controller
{
        use ApiPaginationResponse;
    public function __construct(
        private readonly SubservienceReviewRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 15);

        $paginator = $this->repo->paginate($perPage);

        // Important: convert paginator items to resource array
        $resourceData = SubservienceReviewResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubservienceReviewRequest $request)
    {
        $review = $this->repo->create($request->validated());

        return (new SubservienceReviewResource($review))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $review = $this->repo->findOrFail($id);

        return new SubservienceReviewResource($review);
} public function update(UpdateSubservienceReviewRequest $request, int $id) { $review = $this->repo->findOrFail($id); $updated = $this->repo->update($review, $request->validated());
        return new SubservienceReviewResource($updated);
    }

    public function destroy(int $id)
    {
        $review = $this->repo->findOrFail($id);

        $this->repo->delete($review);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

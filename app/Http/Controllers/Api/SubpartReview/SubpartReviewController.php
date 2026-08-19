<?php

namespace App\Http\Controllers\Api\SubpartReview;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubpartReview\StoreSubpartReviewRequest;
use App\Http\Requests\SubpartReview\UpdateSubpartReviewRequest;
use App\Http\Resources\SubpartReview\SubpartReviewResource;
use App\Repositories\Interfaces\SubpartReviewRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubpartReviewController extends Controller
{
            use ApiPaginationResponse;

    public function __construct(
        private readonly SubpartReviewRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $paginator = $this->repo->paginate($perPage);
        $resourceData = SubpartReviewResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubpartReviewRequest $request)
    {
        $review = $this->repo->create($request->validated());

        return (new SubpartReviewResource($review))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubpartReviewResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubpartReviewRequest $request, int $id)
    {
        $review = $this->repo->findOrFail($id);
        $updated = $this->repo->update($review, $request->validated());

        return new SubpartReviewResource($updated);
    }

    public function destroy(int $id)
    {
        $review = $this->repo->findOrFail($id);
        $this->repo->delete($review);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

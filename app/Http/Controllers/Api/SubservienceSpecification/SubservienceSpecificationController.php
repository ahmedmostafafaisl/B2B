<?php

namespace App\Http\Controllers\Api\SubservienceSpecification;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubservienceSpecification\BulkStoreSubservienceSpecificationRequest;
use App\Http\Requests\SubservienceSpecification\StoreSubservienceSpecificationRequest;
use App\Http\Requests\SubservienceSpecification\UpdateSubservienceSpecificationRequest;
use App\Http\Resources\SubservienceSpecification\SubservienceSpecificationResource;
use App\Repositories\Interfaces\SubservienceSpecificationRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubservienceSpecificationController extends Controller
{
        use ApiPaginationResponse;
    public function __construct(
        private readonly SubservienceSpecificationRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 15);

        $paginator = $this->repo->paginate($perPage);

        // Important: convert paginator items to resource array
        $resourceData = SubservienceSpecificationResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubservienceSpecificationRequest $request)
    {
        $spec = $this->repo->create($request->validated());

        return (new SubservienceSpecificationResource($spec))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $spec = $this->repo->findOrFail($id);

        return new SubservienceSpecificationResource($spec);
    }

    public function update(UpdateSubservienceSpecificationRequest $request, int $id)
    {
        $spec = $this->repo->findOrFail($id);

        $updated = $this->repo->update($spec, $request->validated());

        return new SubservienceSpecificationResource($updated);
    }

    public function destroy(int $id)
    {
        $spec = $this->repo->findOrFail($id);

        $this->repo->delete($spec);

        return response()->json(['message' => 'Deleted successfully']);
} public function bulkStore(BulkStoreSubservienceSpecificationRequest $request) { $items = $request->validated()['items']; $created = $this->repo->bulkCreate($items);
    return SubservienceSpecificationResource::collection($created)
        ->response()
        ->setStatusCode(201);
} }

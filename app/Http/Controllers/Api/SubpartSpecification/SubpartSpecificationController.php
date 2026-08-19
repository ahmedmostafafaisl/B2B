<?php

namespace App\Http\Controllers\Api\SubpartSpecification;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubpartSpecification\StoreSubpartSpecificationRequest;
use App\Http\Requests\SubpartSpecification\UpdateSubpartSpecificationRequest;
use App\Http\Resources\SubpartSpecification\SubpartSpecificationResource;
use App\Repositories\Interfaces\SubpartSpecificationRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubpartSpecificationController extends Controller
{
        use ApiPaginationResponse;

    public function __construct(
        private readonly SubpartSpecificationRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $paginator = $this->repo->paginate($perPage);

        $resourceData = SubpartSpecificationResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubpartSpecificationRequest $request)
    {
        $spec = $this->repo->create($request->validated());

        return (new SubpartSpecificationResource($spec))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubpartSpecificationResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubpartSpecificationRequest $request, int $id)
    {
        $spec = $this->repo->findOrFail($id);

        $updated = $this->repo->update($spec, $request->validated());

        return new SubpartSpecificationResource($updated);
    }

    public function destroy(int $id)
    {
        $spec = $this->repo->findOrFail($id);
        $this->repo->delete($spec);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

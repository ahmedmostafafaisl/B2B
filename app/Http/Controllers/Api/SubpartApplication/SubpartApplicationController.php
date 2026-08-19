<?php

namespace App\Http\Controllers\Api\SubpartApplication;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubpartApplication\StoreSubpartApplicationRequest;
use App\Http\Requests\SubpartApplication\UpdateSubpartApplicationRequest;
use App\Http\Resources\SubpartApplication\SubpartApplicationResource;
use App\Repositories\Interfaces\SubpartApplicationRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubpartApplicationController extends Controller
{

 use ApiPaginationResponse;
    public function __construct(
        private readonly SubpartApplicationRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $subPartId = $request->filled('sub_part_id') ? (int) $request->integer('sub_part_id') : null;

        $paginator = $this->repo->paginate($perPage, $subPartId);

        $resourceData = SubpartApplicationResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubpartApplicationRequest $request)
    {
        $application = $this->repo->create($request->validated());

        return (new SubpartApplicationResource($application))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubpartApplicationResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubpartApplicationRequest $request, int $id)
    {
        $application = $this->repo->findOrFail($id);
        $updated = $this->repo->update($application, $request->validated());

        return new SubpartApplicationResource($updated);
    }

    public function destroy(int $id)
    {
        $application = $this->repo->findOrFail($id);
        $this->repo->delete($application);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

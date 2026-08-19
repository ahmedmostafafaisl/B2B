<?php

namespace App\Http\Controllers\Api\SubpartModel;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubpartModel\StoreSubpartModelRequest;
use App\Http\Requests\SubpartModel\UpdateSubpartModelRequest;
use App\Http\Resources\SubpartModel\SubpartModelResource;
use App\Repositories\Interfaces\SubpartModelRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubpartModelController extends Controller
{
     use ApiPaginationResponse;
    public function __construct(
        private readonly SubpartModelRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $subPartId = $request->filled('sub_part_id') ? (int) $request->integer('sub_part_id') : null;

        $paginator = $this->repo->paginate($perPage, $subPartId);
        $resourceData = SubpartModelResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);

    }

    public function store(StoreSubpartModelRequest $request)
    {
        $model = $this->repo->create(
            $request->validated(),
            $request->file('image')
        );

        return (new SubpartModelResource($model))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubpartModelResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubpartModelRequest $request, int $id)
    {
        $model = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $model,
            $request->validated(),
            $request->file('image')
        );

        return new SubpartModelResource($updated);
    }

    public function destroy(int $id)
    {
        $model = $this->repo->findOrFail($id);
        $this->repo->delete($model);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

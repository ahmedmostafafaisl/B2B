<?php

namespace App\Http\Controllers\Api\SubServiceModel;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubServiceModel\StoreSubServiceModelRequest;
use App\Http\Requests\SubServiceModel\UpdateSubServiceModelRequest;
use App\Http\Resources\SubServiceModel\SubServiceModelResource;
use App\Repositories\Interfaces\SubServiceModelRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubServiceModelController extends Controller
{
    use ApiPaginationResponse;
    public function __construct(
        private readonly SubServiceModelRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);

        $paginator = $this->repo->paginate($perPage);
        // Important: convert paginator items to resource array
        $resourceData = SubServiceModelResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);

    }

    public function store(StoreSubServiceModelRequest $request)
    {
        $model = $this->repo->create(
            $request->validated(),
            $request->file('image')
        );

        return (new SubServiceModelResource($model))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubServiceModelResource(
            $this->repo->findOrFail($id)
        );
    }

    public function update(UpdateSubServiceModelRequest $request, int $id)
    {
        $model = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $model,
            $request->validated(),
            $request->file('image')
        );

        return new SubServiceModelResource($updated);
    }

    public function destroy(int $id)
    {
        $model = $this->repo->findOrFail($id);

        $this->repo->delete($model);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

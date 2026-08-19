<?php

namespace App\Http\Controllers\Api\SubpartFeature;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubpartFeature\StoreSubpartFeatureRequest;
use App\Http\Requests\SubpartFeature\UpdateSubpartFeatureRequest;
use App\Http\Resources\SubpartFeature\SubpartFeatureResource;
use App\Repositories\Interfaces\SubpartFeatureRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubpartFeatureController extends Controller
{
     use ApiPaginationResponse;
    public function __construct(
        private readonly SubpartFeatureRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $subPartId = $request->filled('sub_part_id') ? (int) $request->integer('sub_part_id') : null;

        $paginator = $this->repo->paginate($perPage, $subPartId);
        $resourceData = SubpartFeatureResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubpartFeatureRequest $request)
    {
        $feature = $this->repo->create(
            $request->validated(),
            $request->file('image')
        );

        return (new SubpartFeatureResource($feature))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubpartFeatureResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubpartFeatureRequest $request, int $id)
    {
        $feature = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $feature,
            $request->validated(),
            $request->file('image')
        );

        return new SubpartFeatureResource($updated);
    }

    public function destroy(int $id)
    {
        $feature = $this->repo->findOrFail($id);
        $this->repo->delete($feature);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

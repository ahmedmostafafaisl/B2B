<?php

namespace App\Http\Controllers\Api\SubServiceFeature;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubServiceFeature\StoreSubServiceFeatureRequest;
use App\Http\Requests\SubServiceFeature\UpdateSubServiceFeatureRequest;
use App\Http\Resources\SubServiceFeature\SubServiceFeatureResource;
use App\Repositories\Interfaces\SubServiceFeatureRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubServiceFeatureController extends Controller
{
        use ApiPaginationResponse;
    public function __construct(
        private readonly SubServiceFeatureRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);

        $paginator = $this->repo->paginate($perPage);

        // Important: convert paginator items to resource array
        $resourceData = SubServiceFeatureResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubServiceFeatureRequest $request)
    {
        $feature = $this->repo->create(
            $request->validated(),
            $request->file('image')
        );

        return (new SubServiceFeatureResource($feature))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubServiceFeatureResource(
            $this->repo->findOrFail($id)
        );
    }

    public function update(UpdateSubServiceFeatureRequest $request, int $id)
    {
        $feature = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $feature,
            $request->validated(),
            $request->file('image')
        );

        return new SubServiceFeatureResource($updated);
    }

    public function destroy(int $id)
    {
        $feature = $this->repo->findOrFail($id);

        $this->repo->delete($feature);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

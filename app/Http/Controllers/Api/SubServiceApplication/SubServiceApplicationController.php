<?php

namespace App\Http\Controllers\Api\SubServiceApplication;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubServiceApplication\StoreSubServiceApplicationRequest;
use App\Http\Requests\SubServiceApplication\UpdateSubServiceApplicationRequest;
use App\Http\Resources\SubServiceApplication\SubServiceApplicationResource;
use App\Repositories\Interfaces\SubServiceApplicationRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubServiceApplicationController extends Controller
{
         use ApiPaginationResponse;
    public function __construct(
        private readonly SubServiceApplicationRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);

        $paginator = $this->repo->paginate($perPage);
           // Important: convert paginator items to resource array
        $resourceData = SubServiceApplicationResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);


    }

    public function store(StoreSubServiceApplicationRequest $request)
    {
        $app = $this->repo->create($request->validated());

        return (new SubServiceApplicationResource($app))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubServiceApplicationResource(
            $this->repo->findOrFail($id)
        );
    }

    public function update(UpdateSubServiceApplicationRequest $request, int $id)
    {
        $app = $this->repo->findOrFail($id);

        $updated = $this->repo->update($app, $request->validated());

        return new SubServiceApplicationResource($updated);
    }

    public function destroy(int $id)
    {
        $app = $this->repo->findOrFail($id);

        $this->repo->delete($app);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

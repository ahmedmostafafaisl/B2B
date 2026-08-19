<?php

namespace App\Http\Controllers\Api\SubservienceDoc;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubservienceDoc\StoreSubservienceDocRequest;
use App\Http\Requests\SubservienceDoc\UpdateSubservienceDocRequest;
use App\Http\Resources\SubservienceDoc\SubservienceDocResource;
use App\Repositories\Interfaces\SubservienceDocRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubservienceDocController extends Controller
{
        use ApiPaginationResponse;
    public function __construct(
        private readonly SubservienceDocRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 15);

        $paginator = $this->repo->paginate($perPage);
 $paginator = $this->repo->paginate($perPage);

        // Important: convert paginator items to resource array
        $resourceData = SubservienceDocResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubservienceDocRequest $request)
    {
        $doc = $this->repo->create(
            $request->validated(),
            $request->file('file')
        );

        return (new SubservienceDocResource($doc))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $doc = $this->repo->findOrFail($id);

        return new SubservienceDocResource($doc);
    }

    public function update(UpdateSubservienceDocRequest $request, int $id)
    {
        $doc = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $doc,
            $request->validated(),
            $request->file('file')
        );

        return new SubservienceDocResource($updated);
    }

    public function destroy(int $id)
    {
        $doc = $this->repo->findOrFail($id);

        $this->repo->delete($doc);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

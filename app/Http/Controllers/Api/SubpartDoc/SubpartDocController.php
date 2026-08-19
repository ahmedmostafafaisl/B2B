<?php

namespace App\Http\Controllers\Api\SubpartDoc;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubpartDoc\StoreSubpartDocRequest;
use App\Http\Requests\SubpartDoc\UpdateSubpartDocRequest;
use App\Http\Resources\SubpartDoc\SubpartDocResource;
use App\Repositories\Interfaces\SubpartDocRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubpartDocController extends Controller
{
     use ApiPaginationResponse;
    public function __construct(
        private readonly SubpartDocRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $paginator = $this->repo->paginate($perPage);
        $resourceData = SubpartDocResource::collection($paginator->items());
        return $this->paginatedResponse($paginator, $resourceData);

    }

    public function store(StoreSubpartDocRequest $request)
    {
        $doc = $this->repo->create(
            $request->validated(),
            $request->file('file')
        );

        return (new SubpartDocResource($doc))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubpartDocResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubpartDocRequest $request, int $id)
    {
        $doc = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $doc,
            $request->validated(),
            $request->file('file')
        );

        return new SubpartDocResource($updated);
    }

    public function destroy(int $id)
    {
        $doc = $this->repo->findOrFail($id);
        $this->repo->delete($doc);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

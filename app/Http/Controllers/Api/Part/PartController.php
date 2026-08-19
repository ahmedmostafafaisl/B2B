<?php

namespace App\Http\Controllers\Api\Part;

use App\Http\Controllers\Controller;
use App\Http\Requests\Part\PartStoreRequest;
use App\Http\Requests\Part\PartUpdateRequest;
use App\Http\Resources\Part\PartResource;
use App\Http\Resources\Part\SinglePartResource;
use App\Repositories\Interfaces\PartRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    use ApiPaginationResponse;

    public function __construct(
        private readonly PartRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $isActive = $request->has('is_active')
            ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)
            : null;

        $paginator = $this->repo->paginate($perPage, $page, $isActive);
        $resourceData = PartResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(PartStoreRequest $request)
    {
        $part = $this->repo->create(
            $request->validated(),
            $request->file('primary_image') ?? null,
            $request->file('banner') ?? null
        );

        return (new PartResource($part))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SinglePartResource($this->repo->findOrFail($id));
    }

    public function update(PartUpdateRequest $request, int $id)
    {
        $part = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $part,
            $request->validated(),
            $request->file('primary_image') ?? null,
            $request->file('banner') ?? null
        );

        return new PartResource($updated);
    }

    public function destroy(int $id)
    {
        $part = $this->repo->findOrFail($id);

        $this->repo->delete($part);

        return response()->json(['message' => 'Deleted successfully']);
    }
}

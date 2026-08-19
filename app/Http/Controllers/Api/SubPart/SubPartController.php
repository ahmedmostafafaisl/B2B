<?php

namespace App\Http\Controllers\Api\SubPart;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubPart\StoreSubPartRequest;
use App\Http\Requests\SubPart\UpdateSubPartRequest;
use App\Http\Resources\SubPart\SubPartResource;
use App\Models\SubPartImage;
use App\Repositories\Interfaces\SubPartRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class SubPartController extends Controller
{
    use ApiPaginationResponse;

    public function __construct(
        private readonly SubPartRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);

        $filters = $request->only(['part_id', 'parent_id']);

        $paginator = $this->repo->paginate($perPage, $filters);
        $resourceData = SubPartResource::collection($paginator->items());

        return $this->paginatedResponse($paginator, $resourceData);
    }

    public function store(StoreSubPartRequest $request)
    {
        $subPart = $this->repo->create(
            $request->validated(),
            $request->file('primary_image'),
            $request->file('image_365'),
            $request->file('banner'),
            $request->file('images'),
        );

        return (new SubPartResource($subPart))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        return new SubPartResource($this->repo->findOrFail($id));
    }

    public function update(UpdateSubPartRequest $request, int $id)
    {
        $subPart = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $subPart,
            $request->validated(),
            $request->file('primary_image'),
            $request->file('image_365'),
            $request->file('banner'),
            $request->file('images'),
        );

        return new SubPartResource($updated);
    }

    public function destroy(int $id)
    {
        $subPart = $this->repo->findOrFail($id);
        $this->repo->delete($subPart);

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function destroyImage(int $imageId)
    {
        $image = SubPartImage::query()->findOrFail($imageId);
        $this->repo->deleteImage($image);

        return response()->json(['message' => 'Image deleted successfully']);
    }
}

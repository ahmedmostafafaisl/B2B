<?php

namespace App\Http\Controllers\Api\Solution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Solution\SolutionStoreRequest;
use App\Http\Requests\Solution\SolutionUpdateRequest;
use App\Http\Resources\Solution\SolutionResource;
use App\Repositories\Interfaces\SolutionRepositoryInterface;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    public function __construct(private SolutionRepositoryInterface $solutions) {}

    public function index(Request $request)
    {
        $result = $this->solutions->index($request);

        return response()->json([
            'items' => SolutionResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(SolutionStoreRequest $request)
    {
        $validated = $request->validated();

        $validated['_icon_file'] = $request->file('icon');
        $validated['_banner_file'] = $request->file('banner');

        $images = $request->file('images', []);
        $primaryNewIndex = $validated['primary_new_index'] ?? null;

        unset($validated['images'], $validated['primary_new_index']);

        $solution = $this->solutions->store($validated, $images, $primaryNewIndex);

        return (new SolutionResource($solution))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $solution = $this->solutions->findOrFail($id);

        return new SolutionResource($solution);
    }

    public function update(SolutionUpdateRequest $request, int $id)
    {
        $solution = $this->solutions->findOrFail($id);

        $validated = $request->validated();

        $validated['_icon_file'] = $request->file('icon');
        $validated['_banner_file'] = $request->file('banner');
        $validated['_remove_icon'] = $request->boolean('remove_icon');
        $validated['_remove_banner'] = $request->boolean('remove_banner');

        $newImages = $request->file('images', []);
        $deletedImageIds = $validated['deleted_image_ids'] ?? [];
        $primaryImageId = $validated['primary_image_id'] ?? null;
        $primaryNewIndex = $validated['primary_new_index'] ?? null;

        unset(
            $validated['images'],
            $validated['deleted_image_ids'],
            $validated['primary_image_id'],
            $validated['primary_new_index']
        );

        $updated = $this->solutions->update(
            $solution,
            $validated,
            $newImages,
            $deletedImageIds,
            $primaryImageId,
            $primaryNewIndex
        );

        return new SolutionResource($updated);
    }

    public function destroy(int $id)
    {
        $solution = $this->solutions->findOrFail($id);
        $this->solutions->delete($solution);

        return response()->json(['message' => 'Solution deleted successfully']);
    }
}

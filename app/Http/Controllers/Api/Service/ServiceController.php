<?php

namespace App\Http\Controllers\Api\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceStoreRequest;
use App\Http\Requests\Service\ServiceUpdateRequest;
use App\Http\Resources\Service\ServiceListResource;
use App\Http\Resources\Service\ServiceResource;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(private ServiceRepositoryInterface $services) {}

    public function index(Request $request)
    {
        $result = $this->services->index($request);

        return response()->json([
            'items' => ServiceListResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(ServiceStoreRequest $request)
    {
        $validated = $request->validated();
        // save primary image if provided, otherwise handle in service layer
        $primaryImage = $request->file('primary_image', null);
        // save additional images if provided
        $images = $request->file('images', []);
        $primaryNewIndex = $validated['primary_new_index'] ?? null;

        unset($validated['primary_image'], $validated['images'], $validated['primary_new_index']);
        $service = $this->services->store($validated, $images, $primaryImage, $primaryNewIndex);

        return response()->json([
            'message' => 'Service created successfully.',
            'data' => new ServiceResource($service),
        ], 201);

    }

    public function show(int $id)
    {
        $service = $this->services->findOrFail($id);

        return new ServiceResource($service);
    }

    public function update(ServiceUpdateRequest $request, int $id)
    {
        $service = $this->services->findOrFail($id);

        $validated = $request->validated();
        // save primary image if provided
        $primaryImage = $request->file('primary_image', null);
        // save new additional images if provided
        $newImages = $request->file('images', []);
        $deletedImageIds = $validated['deleted_image_ids'] ?? [];
        $primaryImageId = $validated['primary_image_id'] ?? null;
        $primaryNewIndex = $validated['primary_new_index'] ?? null;

        unset(
            $validated['primary_image'],
            $validated['images'],
            $validated['deleted_image_ids'],
            $validated['primary_image_id'],
            $validated['primary_new_index']
        );

        $updated = $this->services->update(
            $service,
            $validated,
            $newImages,
            $deletedImageIds,
            $primaryImageId,
            $primaryImage,
            $primaryNewIndex
        );

        return response()->json([
            'message' => 'Service updated successfully.',
            'data' => new ServiceResource($service),
        ], 201);

    }

    public function destroy(int $id)
    {
        $service = $this->services->findOrFail($id);
        $this->services->delete($service);

        return response()->json(['message' => 'Service deleted successfully']);
    }
}

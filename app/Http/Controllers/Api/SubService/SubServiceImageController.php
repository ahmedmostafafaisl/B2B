<?php

namespace App\Http\Controllers\Api\SubService;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubServiceImagesUploadRequest;
use App\Http\Resources\SubService\SubServiceResource;
use App\Models\SubService;
use App\Models\SubServiceImage;
use App\Repositories\Interfaces\SubServiceImageRepositoryInterface;

class SubServiceImageController extends Controller
{
    public function __construct(private SubServiceImageRepositoryInterface $images) {}

    // POST /sub-services/{id}/images
    public function upload(SubServiceImagesUploadRequest $request, int $id)
    {
        $subService = SubService::query()->findOrFail($id);

        $images = $request->file('images', []);
        $primaryNewIndex = $request->input('primary_new_index');

        $subService = $this->images->uploadForSubService($subService, $images, $primaryNewIndex);

        return new SubServiceResource($subService);
    }

    // DELETE /sub-services/images/{imageId}
    public function destroy(int $imageId)
    {
        $image = SubServiceImage::query()->findOrFail($imageId);
        $this->images->deleteImage($image);

        return response()->json(['message' => 'Image deleted successfully']);
    }

    // PATCH /sub-services/images/{imageId}/primary
    public function setPrimary(int $imageId)
    {
        $image = SubServiceImage::query()->findOrFail($imageId);
        $subService = $this->images->setPrimary($image);

        return new SubServiceResource($subService);
    }
}

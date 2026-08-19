<?php

namespace App\Http\Controllers\Api\Service;

use App\Models\Service;
use App\Models\ServiceImage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Service\ServiceResource;
use App\Http\Requests\Service\ServiceImagesUploadRequest;
use App\Repositories\Interfaces\ServiceImageRepositoryInterface;

class ServiceImageController extends Controller
{
    public function __construct(private ServiceImageRepositoryInterface $serviceImages)
    {
    }

    /**
     * POST /services/{id}/images
     * Upload images only for a service
     */
    public function upload(ServiceImagesUploadRequest $request, int $id)
    {
        $service = Service::query()->findOrFail($id);

        $images = $request->file('images', []);
        $primaryNewIndex = $request->input('primary_new_index');

        $service = $this->serviceImages->uploadForService($service, $images, $primaryNewIndex);

        return new ServiceResource($service);
    }

    /**
     * DELETE /services/images/{imageId}
     */
    public function destroy(int $imageId)
    {
        $image = ServiceImage::query()->findOrFail($imageId);

        $this->serviceImages->deleteImage($image);

        return response()->json(['message' => 'Image deleted successfully']);
    }

    /**
     * PATCH /services/images/{imageId}/primary
     */
    public function setPrimary(int $imageId)
    {
        $image = ServiceImage::query()->findOrFail($imageId);

        $service = $this->serviceImages->setPrimary($image);

        return new ServiceResource($service);
    }
}

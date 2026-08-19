<?php

namespace App\Http\Controllers\Api\Solution;

use App\Models\Solution;
use App\Models\SolutionImage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Solution\SolutionResource;
use App\Http\Requests\Solution\SolutionImagesUploadRequest;
use App\Repositories\Interfaces\SolutionImageRepositoryInterface;

class SolutionImageController extends Controller
{
    public function __construct(private SolutionImageRepositoryInterface $solutionImages)
    {
    }

    /**
     * POST /solutions/{id}/images
     */
    public function upload(SolutionImagesUploadRequest $request, int $id)
    {
        $solution = Solution::query()->findOrFail($id);

        $images = $request->file('images', []);
        $primaryNewIndex = $request->input('primary_new_index');

        $solution = $this->solutionImages->uploadForSolution($solution, $images, $primaryNewIndex);

        return new SolutionResource($solution);
    }

    /**
     * DELETE /solutions/images/{imageId}
     */
    public function destroy(int $imageId)
    {
        $image = SolutionImage::query()->findOrFail($imageId);

        $this->solutionImages->deleteImage($image);

        return response()->json(['message' => 'Image deleted successfully']);
    }

    /**
     * PATCH /solutions/images/{imageId}/primary
     */
    public function setPrimary(int $imageId)
    {
        $image = SolutionImage::query()->findOrFail($imageId);

        $solution = $this->solutionImages->setPrimary($image);

        return new SolutionResource($solution);
    }
}

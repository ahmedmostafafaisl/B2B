<?php

namespace App\Repositories\SubService;

use App\Models\SubService;
use App\Models\SubServiceImage;
use App\Repositories\Interfaces\SubServiceImageRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubServiceImageRepository implements SubServiceImageRepositoryInterface
{
    public function uploadForSubService(SubService $subService, array $images = [], ?int $primaryNewIndex = null): SubService
    {
        return DB::transaction(function () use ($subService, $images, $primaryNewIndex) {
            $created = [];

            foreach ($images as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->store('sub_services', 's3');

                $created[] = SubServiceImage::create([
                    'sub_service_id' => $subService->id,
                    'image' => $path,
                ]);
            }

            // set primary_image using new index
            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $subService->update(['primary_image' => $created[$primaryNewIndex]->image]);
            } else {
                // ensure primary_image exists if empty
                if (empty($subService->primary_image) && ! empty($created)) {
                    $subService->update(['primary_image' => $created[0]->image]);
                }
            }

            return $subService->refresh()->load('images');
        });
    }

    public function deleteImage(SubServiceImage $image): void
    {
        DB::transaction(function () use ($image) {
            $subService = $image->subService()->firstOrFail();
            $wasPrimary = $subService->primary_image === $image->image;

            $this->deleteFileIfExists($image->image);
            $image->delete();

            if ($wasPrimary) {
                $next = SubServiceImage::query()
                    ->where('sub_service_id', $subService->id)
                    ->orderBy('id')
                    ->first();

                $subService->update(['primary_image' => $next?->image]);
            }
        });
    }

    public function setPrimary(SubServiceImage $image): SubService
    {
        return DB::transaction(function () use ($image) {
            $subService = $image->subService()->firstOrFail();
            $subService->update(['primary_image' => $image->image]);

            return $subService->refresh()->load('images');
        });
    }

    private function deleteFileIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('s3');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}

<?php

namespace App\Repositories\Service;

use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Interfaces\ServiceImageRepositoryInterface;

class ServiceImageRepository implements ServiceImageRepositoryInterface
{
    public function uploadForService(Service $service, array $images = [], ?int $primaryNewIndex = null): Service
    {
        return DB::transaction(function () use ($service, $images, $primaryNewIndex) {
            $created = [];

            foreach ($images as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->store('services', 's3');

                $created[] = ServiceImage::create([
                    'service_id' => $service->id,
                    'image' => $path,
                    'is_primary' => false,
                ]);
            }

            // if requested, set primary from new uploads
            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $this->setPrimary($created[$primaryNewIndex]);
            } else {
                // ensure at least one primary if none exists and we uploaded something
                $hasPrimary = ServiceImage::query()
                    ->where('service_id', $service->id)
                    ->where('is_primary', true)
                    ->exists();

                if (!$hasPrimary && !empty($created)) {
                    $this->setPrimary($created[0]);
                }
            }

            return $service->refresh()->load(['images', 'primaryImage']);
        });
    }

    public function deleteImage(ServiceImage $image): void
    {
        DB::transaction(function () use ($image) {
            $serviceId = $image->service_id;
            $wasPrimary = (bool) $image->is_primary;

            $this->deleteImageFileIfExists($image->image);
            $image->delete();

            // if deleted image was primary, choose another primary (if exists)
            if ($wasPrimary) {
                $next = ServiceImage::query()
                    ->where('service_id', $serviceId)
                    ->orderBy('id')
                    ->first();

                if ($next) {
                    $this->setPrimary($next);
                }
            }
        });
    }

    public function setPrimary(ServiceImage $image): Service
    {
        return DB::transaction(function () use ($image) {
            ServiceImage::query()
                ->where('service_id', $image->service_id)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);

            return $image->service()->firstOrFail()->load(['images', 'primaryImage']);
        });
    }

    private function deleteImageFileIfExists(?string $path): void
    {
        if (!$path) return;

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}

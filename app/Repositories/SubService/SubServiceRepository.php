<?php

namespace App\Repositories\SubService;

use App\Models\SubService;
use App\Models\SubServiceImage;
use App\Repositories\Interfaces\SubServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubServiceRepository implements SubServiceRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = SubService::query()
            ->with(['images'])
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): SubService
    {
        return SubService::query()
            ->with(['images', 'faqs'])
            ->findOrFail($id);
    }

    public function store(array $data, array $images = [], $primaryImage = null, $image_365 = null): SubService
    {
        return DB::transaction(function () use ($data, $images, $primaryImage, $image_365) {
            $subService = SubService::create($data);

            if ($primaryImage instanceof UploadedFile) {
                $path = $primaryImage->store('sub_services', 's3');
                $subService->update(['primary_image' => $path]);
            }

            if ($image_365 instanceof UploadedFile) {
                $path = $image_365->store('sub_services', 's3');
                $subService->update(['image_365' => $path]);
            }

            if (! empty($images)) {
                $this->storeImages($subService->id, $images);
            }

            return $this->findOrFail($subService->id);
        });
    }

    public function update(SubService $subService, array $data, $primaryImage = null, array $newImages = [], array $deletedImageIds = [], $image_365 = null): SubService
    {
        return DB::transaction(function () use (
            $subService,
            $data,
            $primaryImage,
            $newImages,
            $deletedImageIds,
            $image_365
        ) {
            $subService->update($data);

            if (! empty($deletedImageIds)) {
                $imgs = SubServiceImage::query()
                    ->where('sub_service_id', $subService->id)
                    ->whereIn('id', $deletedImageIds)
                    ->get();

                foreach ($imgs as $img) {
                    $this->deleteFileIfExists($img->image);
                    $img->delete();
                }
            }

            if ($image_365 instanceof UploadedFile) {
                $path = $image_365->store('sub_services', 's3');
                $subService->update(['image_365' => $path]);
            }

            // Store new gallery images (replaces all existing ones)
            if (! empty($newImages)) {
                $existingImages = SubServiceImage::query()
                    ->where('sub_service_id', $subService->id)
                    ->get();

                foreach ($existingImages as $img) {
                    $this->deleteFileIfExists($img->image);
                    $img->delete();
                }

                $this->storeImages($subService->id, $newImages);
            }

            // Resolve primary_image with clear priority:
            // 1. Explicit primary_image upload → always wins
            // 2. No upload → keep existing if still valid
            // 3. Existing is gone (deleted/replaced) → fall back to first gallery image
            if ($primaryImage instanceof UploadedFile) {
                // Priority 1: explicit upload always wins
                $path = $primaryImage->store('sub_services', 's3');
                $subService->update(['primary_image' => $path]);
            } else {
                $fresh = $subService->fresh();
                $currentPrimary = $fresh->primary_image;

                $primaryStillValid = $currentPrimary && SubServiceImage::query()
                    ->where('sub_service_id', $subService->id)
                    ->where('image', $currentPrimary)
                    ->exists();

                if (! $primaryStillValid) {
                    // Priority 2/3: fall back to first gallery image (or null if none)
                    $first = SubServiceImage::query()
                        ->where('sub_service_id', $subService->id)
                        ->orderBy('id')
                        ->first();

                    $subService->update(['primary_image' => $first?->image]);
                }
                // else: current primary is still valid, leave it untouched
            }

            return $this->findOrFail($subService->id);
        });
    }

    public function delete(SubService $subService): void
    {
        DB::transaction(function () use ($subService) {
            $subService->load('images');

            foreach ($subService->images as $img) {
                $this->deleteFileIfExists($img->image);
            }

            $this->deleteFileIfExists($subService->primary_image);
            $this->deleteFileIfExists($subService->image_365);

            $subService->delete();
        });
    }

    private function storeImages(int $subServiceId, array $images): array
    {
        $created = [];

        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('sub_services', 's3');

            $created[] = SubServiceImage::create([
                'sub_service_id' => $subServiceId,
                'image' => $path,
            ]);
        }

        return $created;
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

    private function paginate($query, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ];
    }
}

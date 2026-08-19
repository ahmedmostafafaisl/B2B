<?php

namespace App\Repositories\Service;

use App\Models\Service;
use App\Models\ServiceImage;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function index(Request $request): array
    {
        $is_active = $request->input('is_active');
        if ($is_active !== null) {
            $is_active = filter_var($is_active, FILTER_VALIDATE_BOOLEAN);

            $query = Service::query()
                ->where('is_active', $is_active)->with([
                    'serviceTypes' => function ($q) {
                        $q->orderBy('sort_order')
                            ->orderBy('id');
                    },
                    'serviceTypes.subServices' => function ($q) {
                        $q->orderBy('sort_order')
                            ->orderBy('id');
                    },
                ])
                ->orderBy('sort_order')
                ->orderByDesc('id');

            return $this->paginate($query, $request);

        }

        $query = Service::query()
            ->with([
                'serviceTypes' => function ($q) {
                    $q->orderBy('sort_order')
                        ->orderBy('id');
                },
                'serviceTypes.subServices' => function ($q) {
                    $q->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Service
    {
        return Service::query()
            ->with([
                'faqs',
                'images',
                'primaryImage',
                'serviceTypes' => function ($q) {
                    $q->orderBy('sort_order')
                        ->orderBy('id');
                },
                'serviceTypes.faqs',
                'serviceTypes.subServices' => function ($q) {
                    $q->orderBy('sort_order')
                        ->orderBy('id');
                },
                'serviceTypes.subServices.specifications',
            ])
            ->findOrFail($id);
    }

    public function store(array $data, array $images = [], $primaryImage = null, ?int $primaryNewIndex = null): Service
    {
        return DB::transaction(function () use ($data, $images, $primaryImage, $primaryNewIndex) {
            $service = Service::create($data);

            if ($primaryImage instanceof UploadedFile) {
                $path = $primaryImage->store('services', 's3');
                $service->update(['primary_image' => $path]);
            }

            $created = $this->storeImages($service->id, $images);

            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $this->setPrimaryImage($service->id, $created[$primaryNewIndex]->id);
            } elseif (! empty($created)) {
                $this->setPrimaryImage($service->id, $created[0]->id);
            }

            return $this->findOrFail($service->id);
        });
    }

    public function update(
        Service $service,
        array $data,
        array $newImages = [],
        array $deletedImageIds = [],
        ?int $primaryImageId = null,
        $primaryImage = null,
        ?int $primaryNewIndex = null
    ): Service {
        return DB::transaction(function () use (
            $service,
            $data,
            $newImages,
            $deletedImageIds,
            $primaryImageId,
            $primaryImage,
            $primaryNewIndex
        ) {
            $service->update($data);

            if (! empty($deletedImageIds)) {
                $imgs = ServiceImage::query()
                    ->where('service_id', $service->id)
                    ->whereIn('id', $deletedImageIds)
                    ->get();

                foreach ($imgs as $img) {
                    $this->deleteImageFileIfExists($img->image);
                    $img->delete();
                }
            }

            if ($primaryImage instanceof UploadedFile) {
                $path = $primaryImage->store('services', 's3');
                $service->update(['primary_image' => $path]);
            }

            $created = $this->storeImages($service->id, $newImages);

            if ($primaryImageId !== null) {
                $exists = ServiceImage::query()
                    ->where('service_id', $service->id)
                    ->where('id', $primaryImageId)
                    ->exists();

                if ($exists) {
                    $this->setPrimaryImage($service->id, $primaryImageId);
                }
            }

            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $this->setPrimaryImage($service->id, $created[$primaryNewIndex]->id);
            }

            $hasPrimary = ServiceImage::query()
                ->where('service_id', $service->id)
                ->where('is_primary', true)
                ->exists();

            if (! $hasPrimary) {
                $first = ServiceImage::query()
                    ->where('service_id', $service->id)
                    ->orderBy('id')
                    ->first();

                if ($first) {
                    $this->setPrimaryImage($service->id, $first->id);
                }
            }

            return $this->findOrFail($service->id);
        });
    }

    public function delete(Service $service): void
    {
        DB::transaction(function () use ($service) {
            $service->load('images');

            foreach ($service->images as $img) {
                $this->deleteImageFileIfExists($img->image);
            }

            $service->delete();
        });
    }

    private function paginate($query, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $query->paginate(
            $perPage,
            ['*'],
            'page',
            $currentPage
        );

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    private function storeImages(int $serviceId, array $images): array
    {
        $created = [];

        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('services', 's3');

            $created[] = ServiceImage::create([
                'service_id' => $serviceId,
                'image' => $path,
                'is_primary' => false,
            ]);
        }

        return $created;
    }

    private function setPrimaryImage(int $serviceId, int $imageId): void
    {
        ServiceImage::query()
            ->where('service_id', $serviceId)
            ->update(['is_primary' => false]);

        ServiceImage::query()
            ->where('service_id', $serviceId)
            ->where('id', $imageId)
            ->update(['is_primary' => true]);
    }

    private function deleteImageFileIfExists(?string $path): void
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

<?php

namespace App\Repositories\Service;

use App\Models\ServiceType;
use App\Repositories\Interfaces\ServiceTypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceTypeRepository implements ServiceTypeRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = ServiceType::query()
            ->with('service')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): ServiceType
    {
        return ServiceType::query()
            ->with('service')
            ->findOrFail($id);
    }

    public function store(array $data, ?UploadedFile $primaryImage = null): ServiceType
    {
        return DB::transaction(function () use ($data, $primaryImage) {
            if ($primaryImage instanceof UploadedFile) {
                $data['primary_image'] = $primaryImage->store('service-types', 's3');
            }
            if (! isset($data['title']) || empty($data['title'])) {
                $data['title'] = $data['name'] ?? 'Untitled';
            }

            $serviceType = ServiceType::create($data);

            return $this->findOrFail($serviceType->id);
        });
    }

    public function update(ServiceType $serviceType, array $data, ?UploadedFile $primaryImage = null): ServiceType
    {
        return DB::transaction(function () use ($serviceType, $data, $primaryImage) {
            if ($primaryImage instanceof UploadedFile) {
                if ($serviceType->primary_image) {
                    $this->deleteImageFileIfExists($serviceType->primary_image);
                }

                $data['primary_image'] = $primaryImage->store('service-types', 's3');
            }

            $serviceType->update($data);

            return $this->findOrFail($serviceType->id);
        });
    }

    public function delete(ServiceType $serviceType): void
    {
        DB::transaction(function () use ($serviceType) {
            if ($serviceType->primary_image) {
                $this->deleteImageFileIfExists($serviceType->primary_image);
            }

            $serviceType->delete();
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
                'total_items' => $paginator->total(),
            ],
        ];
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

<?php

namespace App\Repositories\Banner;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerRepository implements BannerRepositoryInterface
{
    public function index(Model $bannerable, Request $request): array
    {
        $query = $bannerable->banners()
            ->orderBy('sort_order')
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function store(Model $bannerable, array $data)
    {
        if (! empty($data['_image_file']) && $data['_image_file'] instanceof UploadedFile) {
            $data['image'] = $data['_image_file']->store('banners', 's3');
        }

        unset($data['_image_file']);

        return $bannerable->banners()->create($data);
    }

    public function update(int $id, array $data)
    {
        $banner = Banner::findOrFail($id);

        if (! empty($data['_remove_image'])) {
            $this->deleteFileIfExists($banner->image);
            $data['image'] = null;
        }

        if (! empty($data['_image_file']) && $data['_image_file'] instanceof UploadedFile) {
            $this->deleteFileIfExists($banner->image);
            $data['image'] = $data['_image_file']->store('banners', 's3');
        }

        unset($data['_image_file'], $data['_remove_image']);

        $banner->update($data);

        return $banner;
    }

    public function find(int $id)
    {
        return Banner::findOrFail($id);
    }

    public function delete(int $id): void
    {
        $banner = Banner::findOrFail($id);

        $this->deleteFileIfExists($banner->image);

        $banner->delete();
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

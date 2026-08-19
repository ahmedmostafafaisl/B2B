<?php

namespace App\Repositories\Solution;

use App\Models\Solution;
use App\Models\SolutionImage;
use App\Repositories\Interfaces\SolutionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SolutionRepository implements SolutionRepositoryInterface
{
    public function index(Request $request): array
    {
        if ($request->filled('is_active')) {
            $is_active = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);

            $query = Solution::query()
                ->where('is_active', $is_active)
                ->orderBy('sort_order')
                ->orderByDesc('id');

            return $this->paginate($query, $request);
        }
        $query = Solution::query()
            ->orderBy('sort_order')
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Solution
    {
        return Solution::query()
            ->with(['images', 'primaryImage'])
            ->findOrFail($id);
    }

    public function store(array $data, array $images = [], ?int $primaryNewIndex = null): Solution
    {
        return DB::transaction(function () use ($data, $images, $primaryNewIndex) {
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            if (! empty($data['_icon_file'])) {
                $data['icon'] = $this->storeIconFile($data['_icon_file']);
            }

            if (! empty($data['_banner_file'])) {
                $data['banner'] = $this->storeBannerFile($data['_banner_file']);
            }

            unset($data['_icon_file'], $data['_banner_file']);

            $solution = Solution::create($data);

            $created = $this->storeImages($solution->id, $images);

            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $this->setPrimaryImage($solution->id, $created[$primaryNewIndex]->id);
            } elseif (! empty($created)) {
                $this->setPrimaryImage($solution->id, $created[0]->id);
            }

            return $this->findOrFail($solution->id);
        });
    }

    public function update(
        Solution $solution,
        array $data,
        array $newImages = [],
        array $deletedImageIds = [],
        ?int $primaryImageId = null,
        ?int $primaryNewIndex = null
    ): Solution {
        return DB::transaction(function () use (
            $solution,
            $data,
            $newImages,
            $deletedImageIds,
            $primaryImageId,
            $primaryNewIndex
        ) {
            if (isset($data['title']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            if (! empty($data['_remove_icon'])) {
                $this->deleteFileIfExists($solution->icon);
                $data['icon'] = null;
            }

            if (! empty($data['_icon_file'])) {
                $this->deleteFileIfExists($solution->icon);
                $data['icon'] = $this->storeIconFile($data['_icon_file']);
            }

            if (! empty($data['_remove_banner'])) {
                $this->deleteFileIfExists($solution->banner);
                $data['banner'] = null;
            }

            if (! empty($data['_banner_file'])) {
                $this->deleteFileIfExists($solution->banner);
                $data['banner'] = $this->storeBannerFile($data['_banner_file']);
            }

            unset(
                $data['_icon_file'],
                $data['_banner_file'],
                $data['_remove_icon'],
                $data['_remove_banner']
            );

            $solution->update($data);

            if (! empty($deletedImageIds)) {
                $imgs = SolutionImage::query()
                    ->where('solution_id', $solution->id)
                    ->whereIn('id', $deletedImageIds)
                    ->get();

                foreach ($imgs as $img) {
                    $this->deleteFileIfExists($img->image);
                    $img->delete();
                }
            }

            $created = $this->storeImages($solution->id, $newImages);

            if ($primaryImageId) {
                $this->setPrimaryImage($solution->id, $primaryImageId);
            }

            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $this->setPrimaryImage($solution->id, $created[$primaryNewIndex]->id);
            }

            return $this->findOrFail($solution->id);
        });
    }

    public function delete(Solution $solution): void
    {
        DB::transaction(function () use ($solution) {
            $this->deleteFileIfExists($solution->icon);
            $this->deleteFileIfExists($solution->banner);

            $solution->load('images');
            foreach ($solution->images as $img) {
                $this->deleteFileIfExists($img->image);
            }

            $solution->delete();
        });
    }

    private function storeImages(int $solutionId, array $images): array
    {
        $created = [];

        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('solutions', 's3');

            $created[] = SolutionImage::create([
                'solution_id' => $solutionId,
                'image' => $path,
                'is_primary' => false,
            ]);
        }

        return $created;
    }

    private function setPrimaryImage(int $solutionId, int $imageId): void
    {
        SolutionImage::query()
            ->where('solution_id', $solutionId)
            ->update(['is_primary' => false]);

        SolutionImage::query()
            ->where('solution_id', $solutionId)
            ->where('id', $imageId)
            ->update(['is_primary' => true]);
    }

    private function storeIconFile($file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('solutions/icons', 's3');
    }

    private function storeBannerFile($file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('solutions/banners', 's3');
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

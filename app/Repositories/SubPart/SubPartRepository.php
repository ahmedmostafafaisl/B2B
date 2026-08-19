<?php

namespace App\Repositories\SubPart;

use App\Models\SubPart;
use App\Models\SubPartImage;
use App\Repositories\Interfaces\SubPartRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubPartRepository implements SubPartRepositoryInterface
{
    private string $disk = 's3';

    private string $dir = 'subparts/images';

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return SubPart::query()
            ->with('allChildren')
            ->when(! empty($filters['part_id']), fn ($q) => $q->where('part_id', $filters['part_id']))
            ->when(! empty($filters['parent_id']), fn ($q) => $q->where('parent_id', $filters['parent_id']))
            // If no parent_id filter, return only root-level sub_parts
            ->when(empty($filters['parent_id']), fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubPart
    {
        return SubPart::query()
            ->with('allChildren')
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $image365 = null, ?UploadedFile $banner = null, ?array $images = null): SubPart
    {
        return DB::transaction(function () use ($data, $primaryImage, $image365, $banner, $images) {
            if ($primaryImage) {
                $data['primary_image'] = $this->storeFile($primaryImage, 'primary');
            }
            if ($image365) {
                $data['image_365'] = $this->storeFile($image365, '365');
            }
            if ($banner) {
                $data['banner'] = $this->storeFile($banner, 'banner');
            }

            /** @var SubPart $subPart */
            $subPart = SubPart::query()->create($data);

            if ($images) {
                foreach ($images as $image) {
                    $subPart->images()->create([
                        'image' => $this->storeFile($image, 'additional'),
                    ]);
                }
            }

            return $subPart->fresh(['allChildren']);
        });
    }

    public function update(SubPart $subPart, array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $image365 = null, ?UploadedFile $banner = null, ?array $images = null): SubPart
    {
        return DB::transaction(function () use ($subPart, $data, $primaryImage, $image365, $banner, $images) {
            if ($primaryImage) {
                $this->deleteIfExists($subPart->primary_image);
                $data['primary_image'] = $this->storeFile($primaryImage, 'primary');
            }

            if ($image365) {
                $this->deleteIfExists($subPart->image_365);
                $data['image_365'] = $this->storeFile($image365, '365');
            }

            if ($banner) {
                $this->deleteIfExists($subPart->banner);
                $data['banner'] = $this->storeFile($banner, 'banner');
            }

            if ($images) {
                foreach ($images as $image) {
                    $subPart->images()->create([
                        'image' => $this->storeFile($image, 'additional'),
                    ]);
                }
            }

            $subPart->update($data);

            return $subPart->fresh(['allChildren']);
        });
    }

    public function delete(SubPart $subPart): void
    {
        DB::transaction(function () use ($subPart) {
            $this->deleteIfExists($subPart->primary_image);
            $this->deleteIfExists($subPart->image_365);

            foreach ($subPart->images as $image) {
                $this->deleteIfExists($image->image);
            }

            // Cascade will handle children via DB constraint
            $subPart->delete();
        });
    }

    private function storeFile(UploadedFile $file, string $type): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = Str::uuid()->toString().'.'.$ext;

        return $file->storeAs("{$this->dir}/{$type}", $name, $this->disk);
    }

    private function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    public function deleteImage(SubPartImage $image): void
    {
        DB::transaction(function () use ($image) {
            $subPartId = $image->sub_part_id;
            $wasPrimary = (bool) $image->is_primary;

            $this->deleteImageFileIfExists($image->image);
            $image->delete();

            // if deleted image was primary, choose another primary (if exists)
            if ($wasPrimary) {
                $next = SubPartImage::query()
                    ->where('sub_part_id', $subPartId)
                    ->orderBy('id')
                    ->first();

                if ($next) {
                    $this->setPrimary($next);
                }
            }
        });
    }

    private function deleteImageFileIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}

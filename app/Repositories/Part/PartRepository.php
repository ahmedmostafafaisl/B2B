<?php

namespace App\Repositories\Part;

use App\Models\Part;
use App\Repositories\Interfaces\PartRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartRepository implements PartRepositoryInterface
{
    private string $disk = 's3';

    private string $dir = 'parts/primary';

    public function paginate(int $perPage = 10, int $page = 1, ?bool $isActive = null): LengthAwarePaginator
    {
        return Part::query()
            ->with('banners', 'subParts', 'subParts.banners', 'subParts.specifications')
            ->when(! is_null($isActive), fn ($q) => $q->where('is_active', $isActive))
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findOrFail(int $id): Part
    {
        return Part::query()->with('banners', 'subParts', 'subParts.banners', 'subParts.specifications', 'subParts.allChildren', 'subParts.images')->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $banner = null): Part
    {
        return DB::transaction(function () use ($data, $primaryImage, $banner) {
            if ($primaryImage) {
                $data['primary_image'] = $this->storeImage($primaryImage);
            }
            if ($banner) {
                $data['banner'] = $this->storeImage($banner);
            }

            $part = Part::query()->create($data);

            return $part->fresh();
        });
    }

    public function update(Part $part, array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $banner = null): Part
    {
        return DB::transaction(function () use ($part, $data, $primaryImage, $banner) {
            if ($primaryImage) {
                if ($part->primary_image && Storage::disk($this->disk)->exists($part->primary_image)) {
                    Storage::disk($this->disk)->delete($part->primary_image);
                }
                $data['primary_image'] = $this->storeImage($primaryImage);
            }
            if ($banner) {
                if ($part->banner && Storage::disk($this->disk)->exists($part->banner)) {
                    Storage::disk($this->disk)->delete($part->banner);
                }
                $data['banner'] = $this->storeImage($banner);
            }

            $part->update($data);

            return $part->fresh();
        });
    }

    public function delete(Part $part): void
    {
        DB::transaction(function () use ($part) {
            if ($part->primary_image && Storage::disk($this->disk)->exists($part->primary_image)) {
                Storage::disk($this->disk)->delete($part->primary_image);
            }
            $part->delete();
        });
    }

    private function storeImage(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = Str::uuid()->toString().'.'.$ext;

        return $file->storeAs($this->dir, $name, $this->disk);
    }
}

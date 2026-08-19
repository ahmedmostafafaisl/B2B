<?php

namespace App\Repositories\SubpartFeature;

use App\Models\SubpartFeature;
use App\Models\SubpartFeatureItem;
use App\Models\SubpartFeatureType;
use App\Repositories\Interfaces\SubpartFeatureRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubpartFeatureRepository implements SubpartFeatureRepositoryInterface
{
    private string $disk = 's3';
    private string $dir  = 'subparts/features';

    public function paginate(int $perPage = 10, ?int $subPartId = null): LengthAwarePaginator
    {
        return SubpartFeature::query()
            ->when($subPartId, fn($q) => $q->where('sub_part_id', $subPartId))
            ->with(['types.items'])
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubpartFeature
    {
        return SubpartFeature::query()
            ->with(['types.items'])
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null): SubpartFeature
    {
        return DB::transaction(function () use ($data, $image) {
            if ($image) {
                $data['image'] = $this->storeImage($image);
            }

            $types = $data['types'] ?? [];
            unset($data['types']);

             $feature = SubpartFeature::query()->create($data);

            $this->replaceTypesAndItems($feature, $types);

            return $this->findOrFail($feature->id);
        });
    }

    public function update(SubpartFeature $feature, array $data, ?UploadedFile $image = null): SubpartFeature
    {
        return DB::transaction(function () use ($feature, $data, $image) {
            if ($image) {
                $this->deleteIfExists($feature->image);
                $data['image'] = $this->storeImage($image);
            }

            $types = $data['types'] ?? null;
            unset($data['types']);

            $feature->update($data);

             if (is_array($types)) {
                $this->replaceTypesAndItems($feature, $types);
            }

            return $this->findOrFail($feature->id);
        });
    }

    public function delete(SubpartFeature $feature): void
    {
        DB::transaction(function () use ($feature) {
            $this->deleteIfExists($feature->image);
            $feature->delete(); // cascade deletes types/items
        });
    }

    private function replaceTypesAndItems(SubpartFeature $feature, array $types): void
    {
         $feature->types()->delete(); // cascade -> items

        foreach ($types as $tIndex => $t) {
            $type = SubpartFeatureType::query()->create([
                'subpart_feature_id' => $feature->id,
                'name' => $t['name'] ?? 'Type',
                'sort_order' => (int) ($t['sort_order'] ?? $tIndex),
            ]);

            $items = $t['items'] ?? [];
            foreach ($items as $iIndex => $it) {
                SubpartFeatureItem::query()->create([
                    'subpart_feature_type_id' => $type->id,
                    'text' => $it['text'] ?? '',
                    'sort_order' => (int) ($it['sort_order'] ?? $iIndex),
                ]);
            }
        }
    }

    private function storeImage(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = Str::uuid()->toString() . '.' . $ext;

        return $file->storeAs($this->dir, $name, $this->disk);
    }

    private function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}

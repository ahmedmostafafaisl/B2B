<?php

namespace App\Repositories\SubServiceFeature;

use App\Models\SubServiceFeature;
use App\Models\SubServiceFeatureItem;
use App\Models\SubServiceFeatureType;
use App\Repositories\Interfaces\SubServiceFeatureRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubServiceFeatureRepository implements SubServiceFeatureRepositoryInterface
{
    private string $disk = 's3';
    private string $dir  = 'subservices/features';

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return SubServiceFeature::query()
            ->with(['types.items'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubServiceFeature
    {
        return SubServiceFeature::query()
            ->with(['types.items'])
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null): SubServiceFeature
    {
        return DB::transaction(function () use ($data, $image) {
            $featureData = $this->onlyFeatureData($data);

            if ($image) {
                $featureData['image'] = $this->storeImage($featureData['sub_service_id'], $image);
            }

            /** @var SubServiceFeature $feature */
            $feature = SubServiceFeature::query()->create($featureData);

            $this->syncTypesAndItems($feature, $data['types'] ?? []);

            return $this->findOrFail($feature->id);
        });
    }

    public function update(SubServiceFeature $feature, array $data, ?UploadedFile $image = null): SubServiceFeature
    {
        return DB::transaction(function () use ($feature, $data, $image) {
            $update = $this->onlyFeatureData($data, $feature);

            if ($image) {
                if ($feature->image && Storage::disk($this->disk)->exists($feature->image)) {
                    Storage::disk($this->disk)->delete($feature->image);
                }
                $update['image'] = $this->storeImage($update['sub_service_id'], $image);
            }

            $feature->update($update);

            // Replace nested structure (simple + reliable)
            if (array_key_exists('types', $data)) {
                $this->deleteTypesAndItems($feature);
                $this->syncTypesAndItems($feature, $data['types'] ?? []);
            }

            return $this->findOrFail($feature->id);
        });
    }

    public function delete(SubServiceFeature $feature): void
    {
        DB::transaction(function () use ($feature) {
            $this->deleteTypesAndItems($feature);

            if ($feature->image_path && Storage::disk($this->disk)->exists($feature->image_path)) {
                Storage::disk($this->disk)->delete($feature->image_path);
            }

            $feature->delete();
        });
    }

    private function onlyFeatureData(array $data, ?SubServiceFeature $feature = null): array
    {
        return [
            'sub_service_id' => $data['sub_service_id'] ?? $feature?->sub_service_id,
            'title' => $data['title'] ?? $feature?->title,
            'sort_order' => $data['sort_order'] ?? $feature?->sort_order ?? 0,
            'is_active' => $data['is_active'] ?? $feature?->is_active ?? true,
        ];
    }

    private function syncTypesAndItems(SubServiceFeature $feature, array $types): void
    {
        foreach ($types as $tIndex => $type) {
            /** @var SubServiceFeatureType $createdType */
            $createdType = SubServiceFeatureType::query()->create([
                'sub_service_feature_id' => $feature->id,
                'name' => $type['name'],
                'sort_order' => $type['sort_order'] ?? ($tIndex + 1),
            ]);

            foreach (($type['items'] ?? []) as $iIndex => $item) {
                SubServiceFeatureItem::query()->create([
                    'sub_service_feature_type_id' => $createdType->id,
                    'text' => $item['text'],
                    'sort_order' => $item['sort_order'] ?? ($iIndex + 1),
                ]);
            }
        }
    }

    private function deleteTypesAndItems(SubServiceFeature $feature): void
    {
        $typeIds = $feature->types()->pluck('id')->all();
        if (!empty($typeIds)) {
            SubServiceFeatureItem::query()->whereIn('sub_service_feature_type_id', $typeIds)->delete();
        }
        $feature->types()->delete();
    }

    private function storeImage(int $subServiceId, UploadedFile $image): string
    {
        $ext = strtolower($image->getClientOriginalExtension() ?: 'jpg');
        $name = Str::uuid()->toString() . '.' . $ext;

        return $image->storeAs(
            "{$this->dir}/{$subServiceId}",
            $name,
            $this->disk
        );
    }
}

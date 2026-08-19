<?php

namespace App\Repositories\SubServiceModel;

use App\Models\SubServiceModel;
use App\Models\SubServiceModelItem;
use App\Models\SubServiceModelSection;
use App\Repositories\Interfaces\SubServiceModelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubServiceModelRepository implements SubServiceModelRepositoryInterface
{
    private string $disk = 's3';
    private string $dir  = 'subservices/models';

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return SubServiceModel::query()
            ->with(['sections.items'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubServiceModel
    {
        return SubServiceModel::query()
            ->with(['sections.items'])
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null): SubServiceModel
    {
        return DB::transaction(function () use ($data, $image) {
            $modelData = $this->onlyModelData($data);

            if ($image) {
                $modelData['image'] = $this->storeImage($modelData['sub_service_id'], $image);
            }


            $model = SubServiceModel::query()->create($modelData);

            $this->syncSectionsAndItems($model, $data['sections'] ?? []);

            return $this->findOrFail($model->id);
        });
    }

    public function update(SubServiceModel $model, array $data, ?UploadedFile $image = null): SubServiceModel
    {
        return DB::transaction(function () use ($model, $data, $image) {
            $update = $this->onlyModelData($data, $model);

            if ($image) {
                if ($model->image && Storage::disk($this->disk)->exists($model->image)) {
                    Storage::disk($this->disk)->delete($model->image);
                }
                $update['image'] = $this->storeImage($update['sub_service_id'], $image);
            }

            $model->update($update);

            // Replace nested structure if sections provided
            if (array_key_exists('sections', $data)) {
                $this->deleteSectionsAndItems($model);
                $this->syncSectionsAndItems($model, $data['sections'] ?? []);
            }

            return $this->findOrFail($model->id);
        });
    }

    public function delete(SubServiceModel $model): void
    {
        DB::transaction(function () use ($model) {
            $this->deleteSectionsAndItems($model);

            if ($model->image_path && Storage::disk($this->disk)->exists($model->image_path)) {
                Storage::disk($this->disk)->delete($model->image_path);
            }

            $model->delete();
        });
    }

    private function onlyModelData(array $data, ?SubServiceModel $model = null): array
    {
        return [
            'sub_service_id' => $data['sub_service_id'] ?? $model?->sub_service_id,
            'title' => $data['title'] ?? $model?->title,
            'sort_order' => $data['sort_order'] ?? $model?->sort_order ?? 0,
            'is_active' => $data['is_active'] ?? $model?->is_active ?? true,
        ];
    }

    private function syncSectionsAndItems(SubServiceModel $model, array $sections): void
    {
        foreach ($sections as $sIndex => $section) {
            /** @var SubServiceModelSection $createdSection */
            $createdSection = SubServiceModelSection::query()->create([
                'sub_service_model_id' => $model->id,
                'title' => $section['title'],
                'sort_order' => $section['sort_order'] ?? ($sIndex + 1),
            ]);

            foreach (($section['items'] ?? []) as $iIndex => $item) {
                SubServiceModelItem::query()->create([
                    'sub_service_model_section_id' => $createdSection->id,
                    'text' => $item['text'],
                    'sort_order' => $item['sort_order'] ?? ($iIndex + 1),
                ]);
            }
        }
    }

    private function deleteSectionsAndItems(SubServiceModel $model): void
    {
        $sectionIds = $model->sections()->pluck('id')->all();

        if (!empty($sectionIds)) {
            SubServiceModelItem::query()
                ->whereIn('sub_service_model_section_id', $sectionIds)
                ->delete();
        }

        $model->sections()->delete();
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

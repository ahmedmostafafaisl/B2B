<?php

namespace App\Repositories\SubpartModel;

use App\Models\SubpartModel;
use App\Models\SubpartModelItem;
use App\Models\SubpartModelSection;
use App\Repositories\Interfaces\SubpartModelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubpartModelRepository implements SubpartModelRepositoryInterface
{
    private string $disk = 's3';
    private string $dir  = 'subparts/models';

    public function paginate(int $perPage = 10, ?int $subPartId = null): LengthAwarePaginator
    {
        return SubpartModel::query()
            ->when($subPartId, fn($q) => $q->where('sub_part_id', $subPartId))
            ->with(['sections.items'])
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubpartModel
    {
        return SubpartModel::query()
            ->with(['sections.items'])
            ->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null): SubpartModel
    {
        return DB::transaction(function () use ($data, $image) {
            if ($image) {
                $data['image'] = $this->storeImage($image);
            }

            $sections = $data['sections'] ?? [];
            unset($data['sections']);

            /** @var SubpartModel $model */
            $model = SubpartModel::query()->create($data);

            $this->replaceSectionsAndItems($model, $sections);

            return $this->findOrFail($model->id);
        });
    }

    public function update(SubpartModel $model, array $data, ?UploadedFile $image = null): SubpartModel
    {
        return DB::transaction(function () use ($model, $data, $image) {
            if ($image) {
                $this->deleteIfExists($model->image);
                $data['image'] = $this->storeImage($image);
            }

            $sections = $data['sections'] ?? null;
            unset($data['sections']);

            $model->update($data);

            // لو sections اتبعتت => replace all
            if (is_array($sections)) {
                $this->replaceSectionsAndItems($model, $sections);
            }

            return $this->findOrFail($model->id);
        });
    }

    public function delete(SubpartModel $model): void
    {
        DB::transaction(function () use ($model) {
            $this->deleteIfExists($model->image);
            $model->delete(); // cascade sections/items
        });
    }

    private function replaceSectionsAndItems(SubpartModel $model, array $sections): void
    {
        $model->sections()->delete(); // cascade items

        foreach ($sections as $sIndex => $s) {
            $section = SubpartModelSection::query()->create([
                'subpart_model_id' => $model->id,
                'title' => $s['title'] ?? 'Section',
                'sort_order' => (int) ($s['sort_order'] ?? $sIndex),
            ]);

            $items = $s['items'] ?? [];
            foreach ($items as $iIndex => $it) {
                SubpartModelItem::query()->create([
                    'subpart_model_section_id' => $section->id,
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

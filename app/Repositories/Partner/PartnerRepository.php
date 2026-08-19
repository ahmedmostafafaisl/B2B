<?php

namespace App\Repositories\Partner;

use App\Models\Partner;
use App\Repositories\Interfaces\PartnerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerRepository implements PartnerRepositoryInterface
{
    private string $disk = 's3';

    private string $dir = 'partners/logos';

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return Partner::query()
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Partner
    {
        return Partner::query()->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $logo = null): Partner
    {
        return DB::transaction(function () use ($data, $logo) {
            if ($logo) {
                $data['logo'] = $this->storeFile($logo);
            }

            return Partner::query()->create($data);
        });
    }

    public function update(Partner $partner, array $data, ?UploadedFile $logo = null): Partner
    {
        return DB::transaction(function () use ($partner, $data, $logo) {
            if ($logo) {
                $this->deleteIfExists($partner->logo);
                $data['logo'] = $this->storeFile($logo);
            }

            $partner->update($data);

            return $partner->fresh();
        });
    }

    public function delete(Partner $partner): void
    {
        DB::transaction(function () use ($partner) {
            $this->deleteIfExists($partner->logo);
            $partner->delete();
        });
    }

    public function bulk(array $rows, array $logos = []): \Illuminate\Support\Collection
    {
        $partners = collect();

        DB::transaction(function () use ($rows, $logos, &$partners) {
            foreach ($rows as $index => $row) {
                $logoPath = null;

                if (! empty($logos[$index]) && $logos[$index] instanceof UploadedFile) {
                    $logoPath = $this->storeFile($logos[$index]);
                }

                $partner = Partner::query()->updateOrCreate(
                    ['name' => $row['name']],
                    array_filter([
                        'is_active' => $row['is_active'] ?? true,
                        'sort_order' => $row['sort_order'] ?? 0,
                        'logo' => $logoPath,
                    ], fn ($value) => ! is_null($value))
                );

                $partners->push($partner->fresh());
            }
        });

        return $partners;
    }

    private function storeFile(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = Str::uuid()->toString().'.'.$ext;

        return $file->storeAs($this->dir, $name, $this->disk);
    }

    private function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}

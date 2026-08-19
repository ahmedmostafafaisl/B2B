<?php

namespace App\Repositories\SubpartDoc;

use App\Models\SubpartDoc;
use App\Repositories\Interfaces\SubpartDocRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubpartDocRepository implements SubpartDocRepositoryInterface
{
    private string $disk = 's3';
    private string $dir  = 'subparts/docs';

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return SubpartDoc::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubpartDoc
    {
        return SubpartDoc::query()->findOrFail($id);
    }

    public function create(array $data, UploadedFile $file): SubpartDoc
    {
        return DB::transaction(function () use ($data, $file) {
            [$path, $original, $size] = $this->storePdf($file);

            $data['file_path'] = $path;
            $data['file_original_name'] = $original;
            $data['file_size'] = $size;

            /** @var SubpartDoc $doc */
            $doc = SubpartDoc::query()->create($data);

            return $doc->fresh();
        });
    }

    public function update(SubpartDoc $doc, array $data, ?UploadedFile $file = null): SubpartDoc
    {
        return DB::transaction(function () use ($doc, $data, $file) {
            if ($file) {
                $this->deleteIfExists($doc->file_path);

                [$path, $original, $size] = $this->storePdf($file);
                $data['file_path'] = $path;
                $data['file_original_name'] = $original;
                $data['file_size'] = $size;
            }

            $doc->update($data);

            return $doc->fresh();
        });
    }

    public function delete(SubpartDoc $doc): void
    {
        DB::transaction(function () use ($doc) {
            $this->deleteIfExists($doc->file_path);
            $doc->delete();
        });
    }

    private function storePdf(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $name = Str::uuid()->toString() . '.' . $ext;

        $path = $file->storeAs($this->dir, $name, $this->disk);

        return [
            $path,
            $file->getClientOriginalName(),
            $file->getSize(),
        ];
    }

    private function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}

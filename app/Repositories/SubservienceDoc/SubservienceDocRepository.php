<?php

namespace App\Repositories\SubservienceDoc;

use App\Models\SubservienceDoc;
use App\Repositories\Interfaces\SubservienceDocRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubservienceDocRepository implements SubservienceDocRepositoryInterface
{
    private string $disk = 's3';
    private string $dir  = 'subservience/docs';

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return SubservienceDoc::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubservienceDoc
    {
        return SubservienceDoc::query()->findOrFail($id);
    }

    public function create(array $data, UploadedFile $file): SubservienceDoc
    {
        return DB::transaction(function () use ($data, $file) {
            $path = $this->storePdf($data['sub_service_id'], $file);

            return SubservienceDoc::query()->create([
                'sub_service_id' => $data['sub_service_id'],
                'title' => $data['title'],
                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);
        });
    }

    public function update(SubservienceDoc $doc, array $data, ?UploadedFile $file = null): SubservienceDoc
    {
        return DB::transaction(function () use ($doc, $data, $file) {
            $update = [
                'title' => $data['title'] ?? $doc->title,
                'sub_service_id' => $data['sub_service_id'] ?? $doc->sub_service_id,
            ];

            if ($file) {
                // delete old file
                if ($doc->file_path && Storage::disk($this->disk)->exists($doc->file_path)) {
                    Storage::disk($this->disk)->delete($doc->file_path);
                }

                $path = $this->storePdf($update['sub_service_id'], $file);
                $update['file_path'] = $path;
                $update['file_original_name'] = $file->getClientOriginalName();
                $update['file_size'] = $file->getSize();
            }

            $doc->update($update);

            return $doc->fresh();
        });
    }

    public function delete(SubservienceDoc $doc): void
    {
        DB::transaction(function () use ($doc) {
            if ($doc->file_path && Storage::disk($this->disk)->exists($doc->file_path)) {
                Storage::disk($this->disk)->delete($doc->file_path);
            }
            $doc->delete();
        });
    }

    private function storePdf(int $subservienceId, UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');

        // you said pdf only - still safe:
        if ($ext !== 'pdf') {
            $ext = 'pdf';
        }

        $name = Str::uuid()->toString() . '.pdf';

        return $file->storeAs(
            "{$this->dir}/{$subservienceId}",
            $name,
            $this->disk
        );
    }
}

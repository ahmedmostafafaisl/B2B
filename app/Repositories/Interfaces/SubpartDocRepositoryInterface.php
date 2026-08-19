<?php

namespace App\Repositories\Interfaces;

use App\Models\SubpartDoc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubpartDocRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function findOrFail(int $id): SubpartDoc;

    public function create(array $data, UploadedFile $file): SubpartDoc;

    public function update(SubpartDoc $doc, array $data, ?UploadedFile $file = null): SubpartDoc;

    public function delete(SubpartDoc $doc): void;
}

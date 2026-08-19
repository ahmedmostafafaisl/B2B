<?php

namespace App\Repositories\Interfaces;

use App\Models\SubservienceDoc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubservienceDocRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): SubservienceDoc;

    public function create(array $data, UploadedFile $file): SubservienceDoc;

    public function update(SubservienceDoc $doc, array $data, ?UploadedFile $file = null): SubservienceDoc;

    public function delete(SubservienceDoc $doc): void;
}

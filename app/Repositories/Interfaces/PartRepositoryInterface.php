<?php

namespace App\Repositories\Interfaces;

use App\Models\Part;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface PartRepositoryInterface
{
    public function paginate(int $perPage = 10, int $page = 1, ?bool $isActive = null): LengthAwarePaginator;

    public function findOrFail(int $id): Part;

    public function create(array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $banner = null): Part;

    public function update(Part $part, array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $banner = null): Part;

    public function delete(Part $part): void;
}

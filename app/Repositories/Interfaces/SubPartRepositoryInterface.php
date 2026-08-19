<?php

namespace App\Repositories\Interfaces;

use App\Models\SubPart;
use App\Models\SubPartImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubPartRepositoryInterface
{
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    public function findOrFail(int $id): SubPart;

    public function create(array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $image365 = null, ?UploadedFile $banner = null, ?array $images = null): SubPart;

    public function update(SubPart $subPart, array $data, ?UploadedFile $primaryImage = null, ?UploadedFile $image365 = null, ?UploadedFile $banner = null, ?array $images = null): SubPart;

    public function delete(SubPart $subPart): void;

    public function deleteImage(SubPartImage $image): void;
}

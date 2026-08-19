<?php

namespace App\Repositories\Interfaces;

use App\Models\SubpartFeature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubpartFeatureRepositoryInterface
{
    public function paginate(int $perPage = 10, ?int $subPartId = null): LengthAwarePaginator;

    public function findOrFail(int $id): SubpartFeature;

    public function create(array $data, ?UploadedFile $image = null): SubpartFeature;

    public function update(SubpartFeature $feature, array $data, ?UploadedFile $image = null): SubpartFeature;

    public function delete(SubpartFeature $feature): void;
}

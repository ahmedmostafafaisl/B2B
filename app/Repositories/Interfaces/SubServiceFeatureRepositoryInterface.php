<?php

namespace App\Repositories\Interfaces;

use App\Models\SubServiceFeature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubServiceFeatureRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): SubServiceFeature;

    public function create(array $data, ?UploadedFile $image = null): SubServiceFeature;

    public function update(SubServiceFeature $feature, array $data, ?UploadedFile $image = null): SubServiceFeature;

    public function delete(SubServiceFeature $feature): void;
}

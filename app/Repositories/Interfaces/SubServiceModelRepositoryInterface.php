<?php

namespace App\Repositories\Interfaces;

use App\Models\SubServiceModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubServiceModelRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): SubServiceModel;

    public function create(array $data, ?UploadedFile $image = null): SubServiceModel;

    public function update(SubServiceModel $model, array $data, ?UploadedFile $image = null): SubServiceModel;

    public function delete(SubServiceModel $model): void;
}

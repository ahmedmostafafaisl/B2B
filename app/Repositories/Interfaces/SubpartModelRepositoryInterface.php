<?php

namespace App\Repositories\Interfaces;

use App\Models\SubpartModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface SubpartModelRepositoryInterface
{
    public function paginate(int $perPage = 10, ?int $subPartId = null): LengthAwarePaginator;

    public function findOrFail(int $id): SubpartModel;

    public function create(array $data, ?UploadedFile $image = null): SubpartModel;

    public function update(SubpartModel $model, array $data, ?UploadedFile $image = null): SubpartModel;

    public function delete(SubpartModel $model): void;
}

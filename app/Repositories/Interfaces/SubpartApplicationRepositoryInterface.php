<?php

namespace App\Repositories\Interfaces;

use App\Models\SubpartApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubpartApplicationRepositoryInterface
{
    public function paginate(int $perPage = 10, ?int $subPartId = null): LengthAwarePaginator;

    public function findOrFail(int $id): SubpartApplication;

    public function create(array $data): SubpartApplication;

    public function update(SubpartApplication $application, array $data): SubpartApplication;

    public function delete(SubpartApplication $application): void;
}

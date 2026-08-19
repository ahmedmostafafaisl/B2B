<?php

namespace App\Repositories\Interfaces;

use App\Models\SubpartSpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubpartSpecificationRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function findOrFail(int $id): SubpartSpecification;

    public function create(array $data): SubpartSpecification;

    public function update(SubpartSpecification $spec, array $data): SubpartSpecification;

    public function delete(SubpartSpecification $spec): void;
}

<?php

namespace App\Repositories\Interfaces;

use App\Models\SubservienceSpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SubservienceSpecificationRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): SubservienceSpecification;

    public function create(array $data): SubservienceSpecification;

    public function update(SubservienceSpecification $spec, array $data): SubservienceSpecification;

    public function delete(SubservienceSpecification $spec): void;

    public function bulkCreate(array $items): Collection;

}

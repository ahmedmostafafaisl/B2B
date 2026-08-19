<?php

namespace App\Repositories\Interfaces;

use App\Models\SubServiceApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubServiceApplicationRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): SubServiceApplication;

    public function create(array $data): SubServiceApplication;

    public function update(SubServiceApplication $app, array $data): SubServiceApplication;

    public function delete(SubServiceApplication $app): void;
}

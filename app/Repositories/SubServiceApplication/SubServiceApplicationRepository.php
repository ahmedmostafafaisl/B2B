<?php

namespace App\Repositories\SubServiceApplication;

use App\Models\SubServiceApplication;
use App\Repositories\Interfaces\SubServiceApplicationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubServiceApplicationRepository implements SubServiceApplicationRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return SubServiceApplication::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubServiceApplication
    {
        return SubServiceApplication::query()->findOrFail($id);
    }

    public function create(array $data): SubServiceApplication
    {
        return SubServiceApplication::query()->create($data);
    }

    public function update(SubServiceApplication $app, array $data): SubServiceApplication
    {
        $app->update($data);
        return $app->fresh();
    }

    public function delete(SubServiceApplication $app): void
    {
        $app->delete();
    }
}

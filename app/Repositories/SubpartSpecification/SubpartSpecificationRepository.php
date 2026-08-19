<?php

namespace App\Repositories\SubpartSpecification;

use App\Models\SubpartSpecification;
use App\Repositories\Interfaces\SubpartSpecificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubpartSpecificationRepository implements SubpartSpecificationRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return SubpartSpecification::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubpartSpecification
    {
        return SubpartSpecification::query()->findOrFail($id);
    }

    public function create(array $data): SubpartSpecification
    {
        return SubpartSpecification::query()->create($data);
    }

    public function update(SubpartSpecification $spec, array $data): SubpartSpecification
    {
        $spec->update($data);
        return $spec->fresh();
    }

    public function delete(SubpartSpecification $spec): void
    {
        $spec->delete();
    }
}

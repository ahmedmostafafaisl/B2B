<?php

namespace App\Repositories\SubpartApplication;

use App\Models\SubpartApplication;
use App\Repositories\Interfaces\SubpartApplicationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubpartApplicationRepository implements SubpartApplicationRepositoryInterface
{
    public function paginate(int $perPage = 10, ?int $subPartId = null): LengthAwarePaginator
    {
        return SubpartApplication::query()
            ->when($subPartId, fn($q) => $q->where('sub_part_id', $subPartId))
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubpartApplication
    {
        return SubpartApplication::query()->findOrFail($id);
    }

    public function create(array $data): SubpartApplication
    {
        return SubpartApplication::query()->create($data);
    }

    public function update(SubpartApplication $application, array $data): SubpartApplication
    {
        $application->update($data);
        return $application->fresh();
    }

    public function delete(SubpartApplication $application): void
    {
        $application->delete();
    }
}

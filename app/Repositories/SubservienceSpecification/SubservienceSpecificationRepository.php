<?php

namespace App\Repositories\SubservienceSpecification;

use App\Models\SubservienceSpecification;
use App\Repositories\Interfaces\SubservienceSpecificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubservienceSpecificationRepository implements SubservienceSpecificationRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return SubservienceSpecification::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubservienceSpecification
    {
        return SubservienceSpecification::query()->findOrFail($id);
    }

    public function create(array $data): SubservienceSpecification
    {
        return SubservienceSpecification::query()->create($data);
    }

    public function update(SubservienceSpecification $spec, array $data): SubservienceSpecification
    {
        $spec->update($data);
        return $spec->fresh();
    }

    public function delete(SubservienceSpecification $spec): void
    {
        $spec->delete();
    }

    public function bulkCreate(array $items): Collection
    {
        return DB::transaction(function () use ($items) {
            $now = now();

            // Build rows for insert
            $rows = array_map(function ($item) use ($now) {
                return [
                    'sub_service_id' => $item['sub_service_id'],
                    'type' => $item['type'],
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $items);


            SubservienceSpecification::query()->insert($rows);


            $subIds = array_values(array_unique(array_column($rows, 'sub_service_id')));
            $types  = array_values(array_unique(array_column($rows, 'type')));

            return SubservienceSpecification::query()
                ->whereIn('sub_service_id', $subIds)
                ->whereIn('type', $types)
                ->where('created_at', $now)
                ->latest('id')
                ->get();
        });
    }
}

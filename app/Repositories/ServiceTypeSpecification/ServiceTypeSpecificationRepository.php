<?php

namespace App\Repositories\ServiceTypeSpecification;

use App\Models\ServiceTypeSpecification;
use App\Repositories\Interfaces\ServiceTypeSpecificationRepositoryInterface;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class ServiceTypeSpecificationRepository implements ServiceTypeSpecificationRepositoryInterface
{
    use PaginationTrait;

    public function index(Request $request): array
    {
        $query = ServiceTypeSpecification::query()
            ->with('serviceType')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->service_type_id, function ($q, $serviceTypeId) {
                $q->where('service_type_id', $serviceTypeId);
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function show(int $id): object
    {
        return ServiceTypeSpecification::with('serviceType')->findOrFail($id);
    }

    public function store(array $data): object
    {
        return ServiceTypeSpecification::create($data);
    }

    public function update(int $id, array $data): object
    {
        $specification = ServiceTypeSpecification::findOrFail($id);
        $specification->update($data);

        return $specification->fresh('serviceType');
    }

    public function destroy(int $id): bool
    {
        $specification = ServiceTypeSpecification::findOrFail($id);

        return $specification->delete();
    }
}

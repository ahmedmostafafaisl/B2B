<?php

namespace App\Http\Controllers\Api\ServiceTypeSpecification;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceTypeSpecification\StoreServiceTypeSpecificationRequest;
use App\Http\Requests\ServiceTypeSpecification\UpdateServiceTypeSpecificationRequest;
use App\Http\Resources\ServiceTypeSpecification\ServiceTypeSpecificationResource;
use App\Repositories\Interfaces\ServiceTypeSpecificationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceTypeSpecificationController extends Controller
{
    public function __construct(
        protected ServiceTypeSpecificationRepositoryInterface $repository
    ) {}

    /**
     * GET /service-type-specifications
     * Filters: ?search=  &service_type_id=  &type=
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->index($request);

        return response()->json([
            'items' => ServiceTypeSpecificationResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * GET /service-type-specifications/{id}
     */
    public function show(int $id): JsonResponse
    {
        $specification = $this->repository->show($id);

        return response()->json([
            'item' => new ServiceTypeSpecificationResource($specification),
        ]);
    }

    /**
     * POST /service-type-specifications
     */
    public function store(StoreServiceTypeSpecificationRequest $request): JsonResponse
    {
        $specification = $this->repository->store($request->validated());

        return response()->json([
            'message' => 'Specification created successfully.',
            'item' => new ServiceTypeSpecificationResource($specification->load('serviceType')),
        ], 201);
    }

    /**
     * PUT /service-type-specifications/{id}
     */
    public function update(UpdateServiceTypeSpecificationRequest $request, int $id): JsonResponse
    {
        $specification = $this->repository->update($id, $request->validated());

        return response()->json([
            'message' => 'Specification updated successfully.',
            'item' => new ServiceTypeSpecificationResource($specification),
        ]);
    }

    /**
     * DELETE /service-type-specifications/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json([
            'message' => 'Specification deleted successfully.',
        ]);
    }
}

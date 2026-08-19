<?php

namespace App\Http\Controllers\Api\ServiceType;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceType\ServiceTypeStoreRequest;
use App\Http\Requests\ServiceType\ServiceTypeUpdateRequest;
use App\Http\Resources\ServiceType\ServiceTypeResource;
use App\Repositories\Interfaces\ServiceTypeRepositoryInterface;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function __construct(private ServiceTypeRepositoryInterface $serviceTypes) {}

    public function index(Request $request)
    {
        $result = $this->serviceTypes->index($request);

        return response()->json([
            'items' => ServiceTypeResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(ServiceTypeStoreRequest $request)
    {
        $validated = $request->validated();
        $primaryImage = $request->file('primary_image', null);

        unset($validated['primary_image']);

        $serviceType = $this->serviceTypes->store($validated, $primaryImage);
        $serviceType->load('faqs', 'subServices');

        return response()->json([
            'message' => 'Service type created successfully.',
            'data' => new ServiceTypeResource($serviceType),
        ], 201);

    }

    public function show(int $id)
    {
        $serviceType = $this->serviceTypes->findOrFail($id);

        return new ServiceTypeResource($serviceType);
    }

    public function update(ServiceTypeUpdateRequest $request, int $id)
    {
        $serviceType = $this->serviceTypes->findOrFail($id);

        $validated = $request->validated();
        $primaryImage = $request->file('primary_image', null);

        unset($validated['primary_image']);

        $updated = $this->serviceTypes->update($serviceType, $validated, $primaryImage);
        $serviceType->load('faqs', 'subServices');

        return response()->json([
            'message' => 'Service type updated successfully.',
            'data' => new ServiceTypeResource($serviceType),
        ], 201);
    }

    public function destroy(int $id)
    {
        $serviceType = $this->serviceTypes->findOrFail($id);
        $this->serviceTypes->delete($serviceType);

        return response()->json([
            'message' => 'Service type deleted successfully',
        ]);
    }
}

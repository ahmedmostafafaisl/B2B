<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private readonly RoleRepositoryInterface $roles)
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {

        $result = $this->roles->index($request);

        return response()->json([
            'status' => true,
            'data' => [
                'items' => RoleResource::collection($result['items']),
                'pagination' => $result['pagination'],
            ],
        ]);
    }

    public function store(RoleStoreRequest $request)
    {
        $role = $this->roles->store($request->validated());

        return response()->json([
            'status' => true,
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(int $id)
    {
        $role = $this->roles->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new RoleResource($role),
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        $updated = $this->roles->update($role, $request->validated());

        return response()->json([
            'status' => true,
            'data' => new RoleResource($updated),
        ]);
    }

    public function destroy(int $id)
    {
        $role = $this->roles->findOrFail($id);
        $this->roles->delete($role);

        return response()->json([
            'status' => true,
            'message' => 'Role deleted. Users who had this role have been set to inactive.',
        ]);
    }
}

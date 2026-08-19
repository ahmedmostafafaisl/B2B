<?php

namespace App\Http\Controllers\Api\SideBar;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuModuleStoreRequest;
use App\Http\Requests\MenuModuleUpdateRequest;
use App\Http\Resources\SideBar\MenuModuleResource;
use App\Repositories\Interfaces\MenuModuleRepositoryInterface;
use Illuminate\Http\Request;

class MenuModuleController extends Controller
{
    public function __construct(private MenuModuleRepositoryInterface $modules) {}

    public function index(Request $request)
    {
        $result = $this->modules->index($request);

        return response()->json([
            'items' => MenuModuleResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(MenuModuleStoreRequest $request)
    {
        $module = $this->modules->store($request->validated());
        return (new MenuModuleResource($module))->response()->setStatusCode(201);
    }

    public function show(int $menu_module)
    {
        $module = $this->modules->findOrFail($menu_module);
        return new MenuModuleResource($module);
    }

    public function update(MenuModuleUpdateRequest $request, int $menu_module)
    {
        $module = $this->modules->findOrFail($menu_module);
        $updated = $this->modules->update($module, $request->validated());
        return new MenuModuleResource($updated);
    }

    public function destroy(int $menu_module)
    {
        $module = $this->modules->findOrFail($menu_module);
        $this->modules->delete($module);

        return response()->json(['message' => 'Module deleted successfully']);
    }
}

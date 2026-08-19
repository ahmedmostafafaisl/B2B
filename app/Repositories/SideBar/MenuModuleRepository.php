<?php

namespace App\Repositories\SideBar;

use App\Models\MenuModule;
use App\Repositories\Interfaces\MenuModuleRepositoryInterface;
use Illuminate\Http\Request;

class MenuModuleRepository implements MenuModuleRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = MenuModule::query()->orderBy('sort_order')->orderByDesc('id');
        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): MenuModule
    {
        return MenuModule::query()->findOrFail($id);
    }

    public function store(array $data): MenuModule
    {
        return MenuModule::create($data);
    }

    public function update(MenuModule $module, array $data): MenuModule
    {
        $module->update($data);
        return $module->refresh();
    }

    public function delete(MenuModule $module): void
    {
        $module->delete();
    }

    private function paginate($query, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages'  => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total_items'  => $paginator->total(),
            ],
        ];
    }
}

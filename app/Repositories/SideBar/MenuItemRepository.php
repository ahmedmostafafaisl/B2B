<?php

namespace App\Repositories\SideBar;

use App\Models\MenuItem;
use App\Repositories\Interfaces\MenuItemRepositoryInterface;
use Illuminate\Http\Request;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = MenuItem::query()
            ->with(['children'])
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('menu_module_id')) {
            $query->where('menu_module_id', (int) $request->menu_module_id);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', (int) $request->parent_id);
        }

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): MenuItem
    {
        return MenuItem::query()->with(['children'])->findOrFail($id);
    }

    public function store(array $data): MenuItem
    {
        // if linkable_type provided => linkable_id must exist
        $this->validateLinkable($data['linkable_type'] ?? null, $data['linkable_id'] ?? null);

        return MenuItem::create($data);
    }

    public function update(MenuItem $item, array $data): MenuItem
    {
        $this->validateLinkable($data['linkable_type'] ?? $item->linkable_type, $data['linkable_id'] ?? $item->linkable_id);

        $item->update($data);
        return $item->refresh();
    }

    public function delete(MenuItem $item): void
    {
        $item->delete();
    }

    private function validateLinkable(?string $type, ?int $id): void
    {
        if (!$type && !$id) return;

        if ($type && !$id) abort(422, 'linkable_id is required when linkable_type is provided');
        if ($id && !$type) abort(422, 'linkable_type is required when linkable_id is provided');

        if ($type === \App\Models\Service::class) {
            \App\Models\Service::query()->findOrFail($id);
        } elseif ($type === \App\Models\SubService::class) {
            \App\Models\SubService::query()->findOrFail($id);
        } else {
            abort(422, 'Invalid linkable_type');
        }
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

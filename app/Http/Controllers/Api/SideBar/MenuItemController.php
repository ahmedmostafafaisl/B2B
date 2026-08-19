<?php

namespace App\Http\Controllers\Api\SideBar;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuItemStoreRequest;
use App\Http\Requests\MenuItemUpdateRequest;
use App\Http\Resources\MenuItemResource;
use App\Repositories\Interfaces\MenuItemRepositoryInterface;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function __construct(private MenuItemRepositoryInterface $items) {}

    public function index(Request $request)
    {
        $result = $this->items->index($request);

        return response()->json([
            'items' => MenuItemResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(MenuItemStoreRequest $request)
    {
        $item = $this->items->store($request->validated());
        return (new MenuItemResource($item))->response()->setStatusCode(201);
    }

    public function show(int $menu_item)
    {
        $item = $this->items->findOrFail($menu_item);
        return new MenuItemResource($item->load('children'));
    }

    public function update(MenuItemUpdateRequest $request, int $menu_item)
    {
        $item = $this->items->findOrFail($menu_item);
        $updated = $this->items->update($item, $request->validated());
        return new MenuItemResource($updated->load('children'));
    }

    public function destroy(int $menu_item)
    {
        $item = $this->items->findOrFail($menu_item);
        $this->items->delete($item);

        return response()->json(['message' => 'Menu item deleted successfully']);
    }
}

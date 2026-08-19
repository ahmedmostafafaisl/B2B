<?php

namespace App\Http\Resources\SideBar;

use Illuminate\Http\Request;
use App\Http\Resources\SideBar\MenuItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'link' => $this->link,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,

            // tree
            'items' => $this->whenLoaded('items', fn () => MenuItemResource::collection($this->items)),
        ];
    }
}

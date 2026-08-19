<?php

namespace App\Http\Resources\SideBar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // resolved_link: لو مرتبط بـ Model نطلع لينك API أو website حسب تصميمك
        $resolved = $this->link;

        if ($this->linkable_type && $this->linkable_id) {
            // عدّل المسارات حسب routes عندك
            if ($this->linkable_type === \App\Models\Service::class) {
                $resolved = "/services/{$this->linkable_id}";
            } elseif ($this->linkable_type === \App\Models\SubService::class) {
                $resolved = "/sub-services/{$this->linkable_id}";
            }
        }

        return [
            'id' => $this->id,
            'menu_module_id' => $this->menu_module_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'link' => $this->link,
            'resolved_link' => $resolved,
            'linkable_type' => $this->linkable_type,
            'linkable_id' => $this->linkable_id,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'children' => $this->whenLoaded('children', fn () => MenuItemResource::collection($this->children)),
        ];
    }
}

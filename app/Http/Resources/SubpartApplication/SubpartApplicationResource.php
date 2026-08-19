<?php

namespace App\Http\Resources\SubpartApplication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubpartApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_part_id' => $this->sub_part_id,
            'title' => $this->title,
            'items' => $this->items,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

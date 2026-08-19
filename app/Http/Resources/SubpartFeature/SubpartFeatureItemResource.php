<?php

namespace App\Http\Resources\SubpartFeature;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubpartFeatureItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'sort_order' => $this->sort_order,
        ];
    }
}

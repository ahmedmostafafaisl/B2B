<?php

namespace App\Http\Resources\SubServiceFeature;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceFeatureItemResource extends JsonResource
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

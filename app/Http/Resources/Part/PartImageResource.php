<?php

namespace App\Http\Resources\Part;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class PartImageResource extends JsonResource
{

    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'part_id' => $this->part_id,
             'image' => $this->image ? $this->s3Url($this->image) : null,
            'is_primary' => (bool) $this->is_primary,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }

    private function s3Url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk('s3')->url($path);
    }
}

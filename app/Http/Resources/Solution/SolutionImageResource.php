<?php

namespace App\Http\Resources\Solution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SolutionImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'solution_id' => $this->solution_id,
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

<?php

namespace App\Http\Resources\Solution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SolutionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'banner' => $this->banner ? $this->s3Url($this->banner) : null,
            'banner_image_url' => $this->banner ? $this->s3Url($this->banner) : null,
            'icon' => $this->icon ? $this->s3Url($this->icon) : null,
            'slug' => $this->slug,
            'description' => $this->description,
            'details' => $this->details ?? [],
            'organizations' => $this->organizations ?? [],
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
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

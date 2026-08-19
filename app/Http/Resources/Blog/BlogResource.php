<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'image' => $this->image ? $this->s3Url($this->image) : null,
            'title' => $this->title,
            'desc' => $this->desc,
            'description_points' => $this->description_points ?? [],
            'slug' => $this->slug,
            // 'sections' => BlogSectionResource::collection($this->whenLoaded('sections')),
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'published_at' => optional($this->published_at)->toISOString(),
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

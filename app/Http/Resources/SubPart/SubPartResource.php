<?php

namespace App\Http\Resources\SubPart;

use App\Http\Resources\SubpartApplication\SubpartApplicationResource;
use App\Http\Resources\SubpartDoc\SubpartDocResource;
use App\Http\Resources\SubpartFeature\SubpartFeatureResource;
use App\Http\Resources\SubpartModel\SubpartModelResource;
use App\Http\Resources\SubpartReview\SubpartReviewResource;
use App\Http\Resources\SubpartSpecification\SubpartSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubPartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'part_id' => $this->part_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'primary_image_url' => $this->primary_image ? $this->s3Url($this->primary_image) : null,
            'banner_url' => $this->banner ? $this->s3Url($this->banner) : null,
            'image_365_url' => $this->image_365 ? $this->s3Url($this->image_365) : null,
            'description_365' => $this->description_365,
            'images' => $this->images
                ->pluck('image')
                ->filter()
                ->map(fn ($path) => $this->s3Url($path))
                ->values()
                ->toArray(),
            'banners' => $this->banners
                ->pluck('banner')
                ->filter()
                ->map(fn ($path) => $this->s3Url($path))
                ->values()
                ->toArray(),
            'faqs' => $this->faqs?->map(fn ($faq) => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'sort_order' => $faq->sort_order,
            ]),
            'specifications' => SubpartSpecificationResource::collection($this->specifications),
            'reviews' => SubpartReviewResource::collection($this->reviews),
            'features' => SubpartFeatureResource::collection($this->features),
            'docs' => SubpartDocResource::collection($this->docs),
            'models' => SubpartModelResource::collection($this->models),
            'applications' => SubpartApplicationResource::collection($this->applications),

            // Recursive children
            'children' => SubPartResource::collection($this->whenLoaded('allChildren')),

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

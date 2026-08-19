<?php

namespace App\Http\Resources\Part;

use App\Http\Resources\Banner\BannerResource;
use App\Http\Resources\SubPart\SubPartResource;
use App\Http\Resources\SubpartSpecification\SubpartSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SinglePartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'primary_image' => $this->primary_image,
            'primary_image_url' => $this->primary_image ? $this->s3Url($this->primary_image) : null,
            'banner' => $this->banner,
            'banner_url' => $this->banner ? $this->s3Url($this->banner) : null,
            'banners' => BannerResource::collection($this->whenLoaded('banners')),

            'sub_parts' => $this->whenLoaded('subParts', function () {
                return $this->subParts->map(function ($subPart) {
                    return [
                        'id' => $subPart->id,
                        'title' => $subPart->title,
                        'is_active' => $subPart->is_active,
                        'slug' => $subPart->slug,
                        'sort_order' => $subPart->sort_order,
                        'description' => $subPart->description,
                        'primary_image_url' => $subPart->primary_image
                            ? $this->s3Url($subPart->primary_image)
                            : null,

                        'banners' => $subPart->relationLoaded('banners')
                            ? BannerResource::collection($subPart->banners)
                            : [],

                        'specifications' => $subPart->relationLoaded('specifications')
                            ? SubpartSpecificationResource::collection($subPart->specifications)
                            : [],
                        'children' => $subPart->relationLoaded('allChildren')
                            ? SubPartResource::collection($subPart->allChildren)
                            : [],
                        'images' => $subPart->relationLoaded('images')
                            ? $subPart->images->map(function ($image) {
                                return [
                                    'id' => $image->id,
                                    'url' => $this->s3Url($image->image),
                                ];
                            })
                            : [],
                    ];
                });
            }),

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

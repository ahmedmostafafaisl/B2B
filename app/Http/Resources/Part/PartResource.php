<?php

namespace App\Http\Resources\Part;

use App\Http\Resources\SubpartSpecification\SubpartSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PartResource extends JsonResource
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

            'sub_parts' => $this->subParts->map(function ($subPart) {
                return [
                    'id' => $subPart->id,
                    'title' => $subPart->title,
                    // 'is_active' => $subPart->is_active,
                    // 'primary_image_url' => $subPart->primary_image_url,
                    'slug' => $subPart->slug,
                    // 'specifications' => SubpartSpecificationResource::collection($subPart->specifications),
                ];
            }),
            //  'faqs' => $this->faqs ? $this->faqs->map(function ($faq) {
            //     return [
            //         'id' => $faq->id,
            //         'question' => $faq->question,
            //         'answer' => $faq->answer,
            //         'sort_order' => $faq->sort_order,
            //     ];
            // }) : null,
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

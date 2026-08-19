<?php

namespace App\Http\Resources\SubService;

use App\Http\Resources\Faq\FaqResource;
use App\Http\Resources\SubServiceApplication\SubServiceApplicationResource;
use App\Http\Resources\SubServiceFeature\SubServiceFeatureResource;
use App\Http\Resources\SubServiceModel\SubServiceModelResource;
use App\Http\Resources\SubservienceDoc\SubservienceDocResource;
use App\Http\Resources\SubservienceReview\SubservienceReviewResource;
use App\Http\Resources\SubservienceSpecification\SubservienceSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->serviceType->service->id,
            'type_code' => $this->serviceType->code,
            'service_type_id' => $this->service_type_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'image_365_url' => $this->image_365 ? $this->s3Url($this->image_365) : null,
            'description_365' => $this->description_365,
            'primary_image_url' => $this->primary_image ? $this->s3Url($this->primary_image) : null,
            'images' => $this->whenLoaded('images', function () {
                return $this->images
                    ->pluck('image')
                    ->filter()
                    ->map(fn ($path) => $this->s3Url($path))
                    ->values()
                    ->toArray();
            }),
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
            'specifications' => SubservienceSpecificationResource::collection($this->specifications),
            'reviews' => SubservienceReviewResource::collection($this->reviews),
            'docs' => SubservienceDocResource::collection($this->docs),
            'features' => SubServiceFeatureResource::collection($this->features),
            'applications' => SubServiceApplicationResource::collection($this->applications),
            'models' => SubServiceModelResource::collection($this->models),
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

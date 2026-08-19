<?php

namespace App\Http\Resources\Service;

use App\Http\Resources\Faq\FaqResource;
use App\Http\Resources\SubservienceSpecification\SubservienceSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'primary_image_url' => $this->primary_image ? $this->s3Url($this->primary_image) : null,

            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),

            'types' => $this->whenLoaded('serviceTypes', function () {
                return $this->serviceTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'code' => $type->code,
                        'name' => $type->name,
                        'title' => $type->title,
                        'subtitle' => $type->subtitle,
                        'description' => $type->description,
                        'primary_image_url' => $type->primary_image ? $this->s3Url($type->primary_image) : null,

                        'faqs' => FaqResource::collection($type->faqs ?? collect()),

                        'sub_services' => $type->subServices->map(function ($subService) {
                            return [
                                'id' => $subService->id,
                                'title' => $subService->title,
                                'description' => $subService->description,
                                'primary_image_url' => $subService->primary_image
                                    ? $this->s3Url($subService->primary_image)
                                    : null,
                                'specifications' => SubservienceSpecificationResource::collection(
                                    $subService->specifications ?? collect()
                                ),
                            ];
                        })->values(),
                    ];
                })->values();
            }),
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

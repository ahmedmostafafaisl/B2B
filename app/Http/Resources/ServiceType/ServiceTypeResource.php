<?php

namespace App\Http\Resources\ServiceType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'primary_image_url' => $this->primary_image ? $this->s3Url($this->primary_image) : null,
            'faqs' => $this->whenLoaded('faqs', function () {
                return $this->faqs->map(function ($faq) {
                    return [
                        'id' => $faq->id,
                        'service_type_id' => $faq->service_type_id,
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                        'is_active' => (bool) $faq->is_active,
                        'sort_order' => (int) $faq->sort_order,
                        'created_at' => optional($faq->created_at)->toISOString(),
                        'updated_at' => optional($faq->updated_at)->toISOString(),
                    ];
                })->values();
            }),
            'sub_services' => $this->whenLoaded('subServices', function () {
                return $this->subServices->map(function ($subService) {
                    return [
                        'id' => $subService->id,
                        'service_type_id' => $subService->service_type_id,
                        'service_id' => $subService->service_id,
                        'type_code' => $subService->type_code,
                        'title' => $subService->title,
                        'slug' => $subService->slug,
                        'description' => $subService->description,
                        'primary_image_url' => $subService->primary_image
                            ? $this->s3Url($subService->primary_image)
                            : null,
                        'sort_order' => (int) $subService->sort_order,
                        'is_active' => (bool) $subService->is_active,
                        'created_at' => optional($subService->created_at)->toISOString(),
                        'updated_at' => optional($subService->updated_at)->toISOString(),
                        'specifications' => $subService->specifications->map(function ($spec) {
                            return [
                                'id' => $spec->id,
                                'sub_service_id' => $spec->sub_service_id,
                                'type' => $spec->type,
                                'title' => $spec->title,
                                'description' => $spec->description,
                                'is_active' => (bool) $spec->is_active,
                                'created_at' => optional($spec->created_at)->toISOString(),
                                'updated_at' => optional($spec->updated_at)->toISOString(),
                            ];
                        })->values(),
                    ];
                })->values();
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

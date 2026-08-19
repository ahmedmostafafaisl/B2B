<?php

namespace App\Http\Resources\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'primary_image_url' => $this->primary_image ? $this->s3Url($this->primary_image) : null,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'types' => $this->whenLoaded('serviceTypes', function () {
                return $this->serviceTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'service_id' => $type->service_id,
                        'code' => $type->code,
                        'name' => $type->name,
                        'title' => $type->title,
                        'subtitle' => $type->subtitle,
                        'description' => $type->description,
                        'primary_image_url' => $type->primary_image ? $this->s3Url($type->primary_image) : null,
                        'is_active' => (bool) $type->is_active,
                        'sort_order' => (int) $type->sort_order,
                        'sub_services' => $type->subServices->map(function ($subService) {
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
                                        'sort_order' => (int) $spec->sort_order,
                                        'created_at' => optional($spec->created_at)->toISOString(),
                                        'updated_at' => optional($spec->updated_at)->toISOString(),
                                    ];
                                })->values(),
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

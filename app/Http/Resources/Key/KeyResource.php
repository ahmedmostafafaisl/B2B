<?php

namespace App\Http\Resources\Key;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'is_active' => (bool) $this->is_active,

            'icon' => $this->icon,
            'icon_url' => $this->icon ? $this->s3Url($this->icon) : null,

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

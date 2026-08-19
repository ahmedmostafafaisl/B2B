<?php

namespace App\Http\Resources\SubService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubServiceImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'url' =>$this->image ? $this->s3Url($this->image) : null,
            'created_at' => optional($this->created_at)->toISOString(),
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

<?php

namespace App\Http\Resources\SubservienceDoc;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubservienceDocResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'title' => $this->title,

         

            'file_url' => $this->file_path ? $this->s3Url($this->file_path) : null,
            'file_original_name' => $this->file_original_name,
            'file_size' => $this->file_size,

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

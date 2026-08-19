<?php

namespace App\Http\Resources\SubpartDoc;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubpartDocResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_part_id' => $this->sub_part_id,
            'title' => $this->title,

            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'file_original_name' => $this->file_original_name,
            'file_size' => $this->file_size,

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

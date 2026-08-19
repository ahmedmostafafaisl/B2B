<?php

namespace App\Http\Resources\SubservienceReview;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubservienceReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'rate' => $this->rate,
            'reviewer_name' => $this->reviewer_name,
            'subject' => $this->subject,
            'comment' => $this->comment,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

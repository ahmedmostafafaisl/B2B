<?php

namespace App\Http\Requests\SubservienceReview;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubservienceReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['sometimes', 'integer', 'exists:sub_services,id'],
            'rate' => ['sometimes', 'integer', 'between:1,5'],
            'reviewer_name' => ['sometimes', 'string', 'max:150'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ];
    }
}

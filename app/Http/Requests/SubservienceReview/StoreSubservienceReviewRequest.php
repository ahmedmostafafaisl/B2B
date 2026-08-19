<?php

namespace App\Http\Requests\SubservienceReview;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubservienceReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'rate' => ['required', 'integer', 'between:1,5'],
            'reviewer_name' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ];
    }
}

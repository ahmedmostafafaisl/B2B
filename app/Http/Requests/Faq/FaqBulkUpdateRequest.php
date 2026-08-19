<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class FaqBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'faqs' => ['required', 'array', 'min:1'],

            'faqs.*.id' => ['required', 'integer', 'exists:faqs,id'],
            'faqs.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['nullable', 'string'],
            'faqs.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'faqs.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}

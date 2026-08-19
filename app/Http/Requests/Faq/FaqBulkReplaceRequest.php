<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class FaqBulkReplaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'faqs' => ['required', 'array'],

            'faqs.*.question' => ['required', 'string', 'max:255'],
            'faqs.*.answer' => ['required', 'string'],
            'faqs.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'faqs.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class BulkPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partners' => ['required', 'array', 'min:1'],
            'partners.*.name' => ['required', 'string', 'max:255'],
            'partners.*.is_active' => ['nullable', 'boolean'],
            'partners.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

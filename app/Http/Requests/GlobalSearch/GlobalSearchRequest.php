<?php

namespace App\Http\Requests\GlobalSearch;

use Illuminate\Foundation\Http\FormRequest;

class GlobalSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => 'required|string|min:1|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'query.required' => 'Search query is required.',
            'query.min' => 'Search query must be at least 1 character.',
            'query.max' => 'Search query must not exceed 255 characters.',
        ];
    }
}

<?php

namespace App\Http\Requests\Part;

use Illuminate\Foundation\Http\FormRequest;

class PartStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'primary_image' => ['nullable', 'image', 'max:5120'], // 5MB
            'banner' => ['nullable', 'image', 'max:5120'], // 5MB
        ];
    }
}

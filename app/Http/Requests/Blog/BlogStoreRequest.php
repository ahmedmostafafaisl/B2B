<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class BlogStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'desc' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blogs,slug'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description_points' => ['nullable', 'array'],
            'description_points.*' => ['string'],
            'published_at' => ['nullable', 'date'],

            'sections' => ['nullable', 'array'],
            'sections.*.type' => ['required_with:sections', 'string', 'in:paragraph,bullets'],
            'sections.*.content' => ['required_with:sections'],
            'sections.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

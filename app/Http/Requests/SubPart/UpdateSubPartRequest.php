<?php

namespace App\Http\Requests\SubPart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'part_id' => ['sometimes', 'nullable', 'integer', 'exists:parts,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:sub_parts,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'primary_image' => ['sometimes', 'image', 'max:5120'],
            'banner' => ['sometimes', 'image', 'max:5120'],
            'image_365' => ['sometimes', 'image', 'max:5120'],
            'description_365' => ['sometimes', 'nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}

<?php

namespace App\Http\Requests\SubPart;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'parent_id' => ['nullable', 'integer', 'exists:sub_parts,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'primary_image' => ['nullable', 'image', 'max:5120'],
            'banner' => ['nullable', 'image', 'max:5120'],
            'image_365' => ['nullable', 'image', 'max:5120'],
            'description_365' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}

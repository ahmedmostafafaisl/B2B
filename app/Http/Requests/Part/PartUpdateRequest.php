<?php

namespace App\Http\Requests\Part;

use Illuminate\Foundation\Http\FormRequest;

class PartUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'primary_image' => ['sometimes', 'image', 'max:5120'],
            'banner' => ['sometimes', 'image', 'max:5120'],
        ];
    }
}

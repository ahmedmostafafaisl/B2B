<?php

namespace App\Http\Requests\Solution;

use Illuminate\Foundation\Http\FormRequest;

class SolutionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:solutions,slug'],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'array'],
            'details.*' => ['string', 'max:255'],
            'organizations' => ['nullable', 'array'],
            'organizations.*' => ['string', 'max:255'],

            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'banner' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'primary_new_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

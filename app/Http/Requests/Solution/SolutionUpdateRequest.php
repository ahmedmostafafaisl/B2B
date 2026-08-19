<?php

namespace App\Http\Requests\Solution;

use Illuminate\Foundation\Http\FormRequest;

class SolutionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeParam = $this->route('solution');
        $solutionId = is_object($routeParam) ? $routeParam->id : (int) $routeParam;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', "unique:solutions,slug,{$solutionId}"],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'array'],
            'details.*' => ['string', 'max:255'],

            'organizations' => ['nullable', 'array'],
            'organizations.*' => ['string', 'max:255'],

            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'banner' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_icon' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],

            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'deleted_image_ids' => ['nullable', 'array'],
            'deleted_image_ids.*' => ['integer', 'exists:solution_images,id'],

            'primary_image_id' => ['nullable', 'integer', 'exists:solution_images,id'],
            'primary_new_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

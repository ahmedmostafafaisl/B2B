<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
            $routeParam = $this->route('service'); // id
        $serviceId = is_object($routeParam) ? $routeParam->id : (int) $routeParam;


      return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            // add new images
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            // delete existing images by ids
            'deleted_image_ids' => ['nullable', 'array'],
            'deleted_image_ids.*' => ['integer', 'exists:service_images,id'],

            // set primary to an existing image id
            'primary_image_id' => ['nullable', 'integer', 'exists:service_images,id'],

            // OR set primary to newly uploaded image index
            'primary_new_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

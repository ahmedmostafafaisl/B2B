<?php

namespace App\Http\Requests\Solution;

use Illuminate\Foundation\Http\FormRequest;

class SolutionImagesUploadRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'primary_new_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

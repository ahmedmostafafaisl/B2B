<?php

namespace App\Http\Requests\Part;

use Illuminate\Foundation\Http\FormRequest;

class PartImagesUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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

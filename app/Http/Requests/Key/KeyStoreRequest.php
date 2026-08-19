<?php

namespace App\Http\Requests\Key;

use Illuminate\Foundation\Http\FormRequest;

class KeyStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255', 'unique:keys,key'],
            'is_active' => ['nullable', 'boolean'],
            // icon image upload
            'icon' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            // remove icon on update usage (not needed here but keeping consistent)
            'remove_icon' => ['nullable', 'boolean'],
        ];
    }
}

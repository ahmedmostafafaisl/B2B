<?php

namespace App\Http\Requests\Key;

use Illuminate\Foundation\Http\FormRequest;

class KeyUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


  public function rules(): array
    {
        $routeParam = $this->route('key_item'); // we will name route param key_item to avoid confusion
        $id = is_object($routeParam) ? $routeParam->id : (int) $routeParam;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'key' => ['sometimes', 'required', 'string', 'max:255', "unique:keys,key,{$id}"],
            'is_active' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'remove_icon' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\ServiceType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeParam = $this->route('service_type');
        $serviceTypeId = is_object($routeParam) ? $routeParam->id : (int) $routeParam;

        return [
            'service_id' => ['sometimes', 'required', 'integer', 'exists:services,id'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('service_types', 'code')->ignore($serviceTypeId),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}

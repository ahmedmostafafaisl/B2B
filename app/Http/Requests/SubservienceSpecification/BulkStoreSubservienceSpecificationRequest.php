<?php

namespace App\Http\Requests\SubservienceSpecification;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreSubservienceSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'items.*.type' => ['required', 'string', 'max:100'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'items is required',
            'items.array' => 'items must be an array',
            'items.*.sub_service_id.required' => 'sub_service_id is required for each item',
            'items.*.type.required' => 'type is required for each item',
            'items.*.title.required' => 'title is required for each item',
        ];
    }
}

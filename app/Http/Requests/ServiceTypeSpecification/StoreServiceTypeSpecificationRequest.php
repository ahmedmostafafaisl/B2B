<?php

namespace App\Http\Requests\ServiceTypeSpecification;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceTypeSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_type_id.required' => 'Service type is required.',
            'service_type_id.exists' => 'Selected service type does not exist.',
            'type.required' => 'Type is required.',
            'title.required' => 'Title is required.',
        ];
    }
}

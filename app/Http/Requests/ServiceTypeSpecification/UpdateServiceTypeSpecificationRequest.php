<?php

namespace App\Http\Requests\ServiceTypeSpecification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceTypeSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type_id' => ['sometimes', 'integer', 'exists:service_types,id'],
            'type' => ['sometimes', 'string', 'max:100'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_type_id.exists' => 'Selected service type does not exist.',
        ];
    }
}

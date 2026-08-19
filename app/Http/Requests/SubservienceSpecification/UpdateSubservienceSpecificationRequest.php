<?php

namespace App\Http\Requests\SubservienceSpecification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubservienceSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['sometimes', 'integer', 'exists:sub_services,id'],
            'type' => ['sometimes', 'string', 'max:100'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}

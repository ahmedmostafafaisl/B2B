<?php

namespace App\Http\Requests\SubservienceSpecification;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubservienceSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // add policy later if needed
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}

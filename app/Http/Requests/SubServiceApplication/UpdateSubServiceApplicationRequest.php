<?php

namespace App\Http\Requests\SubServiceApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubServiceApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if (is_string($this->items)) {
            $this->merge(['items' => json_decode($this->items, true)]);
        }
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['sometimes', 'integer', 'exists:sub_services,id'],
            'title' => ['sometimes', 'string', 'max:255'],

            'items' => ['sometimes', 'array', 'min:1'],
            'items.*' => ['required_with:items', 'string', 'max:255'],

            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

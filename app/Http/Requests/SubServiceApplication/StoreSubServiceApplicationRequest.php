<?php

namespace App\Http\Requests\SubServiceApplication;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubServiceApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // items may come as JSON string from form-data
        if (is_string($this->items)) {
            $this->merge(['items' => json_decode($this->items, true)]);
        }
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'title' => ['required', 'string', 'max:255'],

            // items = array of strings
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'string', 'max:255'],

            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

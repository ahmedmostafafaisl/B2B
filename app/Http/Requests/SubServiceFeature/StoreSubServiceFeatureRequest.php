<?php

namespace App\Http\Requests\SubServiceFeature;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreSubServiceFeatureRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // types may arrive as JSON string in multipart
        if (is_string($this->types)) {
            $decoded = json_decode($this->types, true);
            $this->merge(['types' => $decoded]);
        }
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'image' => ['nullable', 'image', 'max:5120'], // 5MB

            'types' => ['required', 'array', 'min:1'],

            'types.*.name' => ['required', 'string', 'max:255'],
            'types.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'types.*.items' => ['required', 'array', 'min:1'],
            'types.*.items.*.text' => ['required', 'string', 'max:255'],
            'types.*.items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        // If json_decode failed -> types becomes null (invalid)
        if (!is_array($this->input('types'))) {
            throw ValidationException::withMessages([
                'types' => ['types must be a valid JSON array.'],
            ]);
        }
    }
}

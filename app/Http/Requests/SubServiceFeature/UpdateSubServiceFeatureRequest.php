<?php

namespace App\Http\Requests\SubServiceFeature;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateSubServiceFeatureRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if (is_string($this->types)) {
            $decoded = json_decode($this->types, true);
            $this->merge(['types' => $decoded]);
        }
    }

    public function rules(): array
    {
        return [
            'sub_service_id' => ['sometimes', 'integer', 'exists:sub_services,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],

            'image' => ['sometimes', 'image', 'max:5120'],

            // If you send types -> validate it (optional)
            'types' => ['sometimes', 'array', 'min:1'],
            'types.*.name' => ['required_with:types', 'string', 'max:255'],
            'types.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'types.*.items' => ['required_with:types', 'array', 'min:1'],
            'types.*.items.*.text' => ['required_with:types', 'string', 'max:255'],
            'types.*.items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        if ($this->has('types') && !is_array($this->input('types'))) {
            throw ValidationException::withMessages([
                'types' => ['types must be a valid JSON array.'],
            ]);
        }
    }
}

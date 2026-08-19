<?php

namespace App\Http\Requests\SubServiceModel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreSubServiceModelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if (is_string($this->sections)) {
            $this->merge(['sections' => json_decode($this->sections, true)]);
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

            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'sections.*.items' => ['required', 'array', 'min:1'],
            'sections.*.items.*.text' => ['required', 'string', 'max:255'],
            'sections.*.items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        if (!is_array($this->input('sections'))) {
            throw ValidationException::withMessages([
                'sections' => ['sections must be a valid JSON array.'],
            ]);
        }
    }
}

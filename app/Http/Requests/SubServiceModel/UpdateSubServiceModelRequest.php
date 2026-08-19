<?php

namespace App\Http\Requests\SubServiceModel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateSubServiceModelRequest extends FormRequest
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
            'sub_service_id' => ['sometimes', 'integer', 'exists:sub_services,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],

            'image' => ['sometimes', 'image', 'max:5120'],

            'sections' => ['sometimes', 'array', 'min:1'],
            'sections.*.title' => ['required_with:sections', 'string', 'max:255'],
            'sections.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'sections.*.items' => ['required_with:sections', 'array', 'min:1'],
            'sections.*.items.*.text' => ['required_with:sections', 'string', 'max:255'],
            'sections.*.items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        if ($this->has('sections') && !is_array($this->input('sections'))) {
            throw ValidationException::withMessages([
                'sections' => ['sections must be a valid JSON array.'],
            ]);
        }
    }
}

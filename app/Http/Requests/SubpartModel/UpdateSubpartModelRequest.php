<?php

namespace App\Http\Requests\SubpartModel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubpartModelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if (is_string($this->sections)) {
            $decoded = json_decode($this->sections, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['sections' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['sometimes','integer','exists:sub_parts,id'],
            'title' => ['sometimes','string','max:255'],
            'image' => ['sometimes','image','max:5120'],
            'sort_order' => ['sometimes','integer','min:0'],
            'is_active' => ['sometimes','boolean'],

             'sections' => ['sometimes','array'],
            'sections.*.title' => ['required_with:sections','string','max:255'],
            'sections.*.sort_order' => ['nullable','integer','min:0'],
            'sections.*.items' => ['nullable','array'],
            'sections.*.items.*.text' => ['required_with:sections.*.items','string','max:255'],
            'sections.*.items.*.sort_order' => ['nullable','integer','min:0'],
        ];
    }
}

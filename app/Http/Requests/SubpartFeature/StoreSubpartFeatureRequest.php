<?php

namespace App\Http\Requests\SubpartFeature;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubpartFeatureRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
         if (is_string($this->types)) {
            $decoded = json_decode($this->types, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['types' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['required','integer','exists:sub_parts,id'],
            'title' => ['required','string','max:255'],
            'image' => ['nullable','image','max:5120'],
            'sort_order' => ['nullable','integer','min:0'],
            'is_active' => ['nullable','boolean'],

            'types' => ['nullable','array'],
            'types.*.name' => ['required_with:types','string','max:255'],
            'types.*.sort_order' => ['nullable','integer','min:0'],

            'types.*.items' => ['nullable','array'],
            'types.*.items.*.text' => ['required_with:types.*.items','string','max:255'],
            'types.*.items.*.sort_order' => ['nullable','integer','min:0'],
        ];
    }
}

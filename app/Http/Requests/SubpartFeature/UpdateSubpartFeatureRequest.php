<?php

namespace App\Http\Requests\SubpartFeature;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubpartFeatureRequest extends FormRequest
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
            'sub_part_id' => ['sometimes','integer','exists:sub_parts,id'],
            'title' => ['sometimes','string','max:255'],
            'image' => ['sometimes','image','max:5120'],
            'sort_order' => ['sometimes','integer','min:0'],
            'is_active' => ['sometimes','boolean'],

            // لو بعت types هنستبدل الكل
            'types' => ['sometimes','array'],
            'types.*.name' => ['required_with:types','string','max:255'],
            'types.*.sort_order' => ['nullable','integer','min:0'],

            'types.*.items' => ['nullable','array'],
            'types.*.items.*.text' => ['required_with:types.*.items','string','max:255'],
            'types.*.items.*.sort_order' => ['nullable','integer','min:0'],
        ];
    }
}

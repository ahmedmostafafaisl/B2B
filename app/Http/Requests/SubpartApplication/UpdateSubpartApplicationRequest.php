<?php

namespace App\Http\Requests\SubpartApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubpartApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['sometimes','integer','exists:sub_parts,id'],
            'title' => ['sometimes','string','max:255'],

            'items' => ['sometimes','array','min:1'],
            'items.*' => [
                'required_with:items',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) && !is_array($value)) {
                        $fail("$attribute must be a string or object.");
                    }
                }
            ],

            'sort_order' => ['sometimes','integer','min:0'],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}

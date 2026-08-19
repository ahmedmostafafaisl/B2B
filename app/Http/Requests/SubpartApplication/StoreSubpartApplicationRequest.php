<?php

namespace App\Http\Requests\SubpartApplication;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubpartApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['required','integer','exists:sub_parts,id'],
            'title' => ['required','string','max:255'],

            // items: array of strings OR objects
            'items' => ['required','array','min:1'],
            'items.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) && !is_array($value)) {
                        $fail("$attribute must be a string or object.");
                    }
                }
            ],

            'sort_order' => ['nullable','integer','min:0'],
            'is_active' => ['nullable','boolean'],
        ];
    }
}

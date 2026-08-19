<?php

namespace App\Http\Requests\SubpartSpecification;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubpartSpecificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['required', 'integer', 'exists:sub_parts,id'],
            'type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}

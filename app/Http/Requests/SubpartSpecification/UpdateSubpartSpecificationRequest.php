<?php

namespace App\Http\Requests\SubpartSpecification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubpartSpecificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['sometimes', 'integer', 'exists:sub_parts,id'],
            'type' => ['sometimes', 'string', 'max:100'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}

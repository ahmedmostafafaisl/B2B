<?php

namespace App\Http\Requests\SubservienceDoc;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubservienceDocRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
                        'sub_service_id' => ['sometimes', 'integer', 'exists:sub_services,id'],

             'title' => ['sometimes', 'string', 'max:255'],
            'file' => ['sometimes', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }
}

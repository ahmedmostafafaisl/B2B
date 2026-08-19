<?php

namespace App\Http\Requests\SubservienceDoc;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubservienceDocRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20MB
        ];
    }
}

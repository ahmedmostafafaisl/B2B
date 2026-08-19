<?php

namespace App\Http\Requests\SubpartDoc;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubpartDocRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['required','integer','exists:sub_parts,id'],
            'title' => ['required','string','max:255'],
            'file' => ['required','file','mimetypes:application/pdf','max:20480'], // 20MB
        ];
    }
}

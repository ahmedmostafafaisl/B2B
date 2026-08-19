<?php

namespace App\Http\Requests\SubpartReview;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubpartReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['required','integer','exists:sub_parts,id'],
            'rate' => ['required','integer','min:1','max:5'],
            'reviewer_name' => ['required','string','max:150'],
            'subject' => ['required','string','max:255'],
            'comment' => ['nullable','string'],
        ];
    }
}

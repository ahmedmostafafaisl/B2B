<?php

namespace App\Http\Requests\SubpartReview;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubpartReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sub_part_id' => ['sometimes','integer','exists:sub_parts,id'],
            'rate' => ['sometimes','integer','min:1','max:5'],
            'reviewer_name' => ['sometimes','string','max:150'],
            'subject' => ['sometimes','string','max:255'],
            'comment' => ['sometimes','nullable','string'],
        ];
    }
}

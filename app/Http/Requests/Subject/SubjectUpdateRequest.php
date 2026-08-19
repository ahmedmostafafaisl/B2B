<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $routeParam = $this->route('subject');
        $subjectId = is_object($routeParam) ? $routeParam->id : (int) $routeParam;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', "unique:subjects,code,{$subjectId}"],
            'description' => ['nullable', 'string'],
        ];
    }
}

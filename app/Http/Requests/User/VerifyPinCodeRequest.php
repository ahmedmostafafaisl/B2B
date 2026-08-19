<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPinCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin_code' => 'required|string|min:4|max:4',
            'phone' => 'required_if:is_auth,false|exists:users,phone',
            'update_version' => 'required|integer',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_auth' => auth('api')->check(),
        ]);
    }

    public function messages()
    {
        return [
            'phone.required_if' => 'The phone field is required when no authenticated user is present.',
        ];
    }
}

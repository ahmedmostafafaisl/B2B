<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class SendContactMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', 'exists:contacts,id'],
            'to_email'   => ['required', 'email', 'max:255'],
            'email_note'  => ['nullable', 'string', 'max:1000'],
            'contact_url' => ['nullable', 'string', 'max:500'],
            'cc'          => ['nullable', 'array'],
            'cc.*'        => ['email', 'max:255'],
        ];
    }
}

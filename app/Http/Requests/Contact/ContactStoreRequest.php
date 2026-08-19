<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'key_id' => ['nullable', 'exists:keys,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string'],
            'status' => ['nullable', 'in:new,in_progress,contacted,closed,offer_price,completed,price_not_accepted,not_serious,needs_follow_up,no_response,awaiting_response,unable_to_contact'],
            'note' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'key_id' => ['nullable', 'exists:keys,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'prod_category' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'source_page' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:new,in_progress,contacted,closed,offer_price,completed,price_not_accepted,not_serious,needs_follow_up,no_response,awaiting_response,unable_to_contact,supplier_registration,inquiry'],
            'note' => ['nullable', 'string'],
            'closing_reasons' => ['nullable', 'array'],
            'closing_reasons.*' => ['integer', 'distinct', 'exists:closing_reasons,id'],
        ];
    }
}

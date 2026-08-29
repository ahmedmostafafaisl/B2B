<?php

namespace App\Http\Requests\EventType;

use App\Repositories\EventType\EventTypeRepository;
use Illuminate\Foundation\Http\FormRequest;

class EventTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255', 'unique:event_types,name'],
            'label' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:' . implode(',', EventTypeRepository::VALID_STATUSES)],
            'requires_approval'    => ['nullable', 'boolean'],
            'affects_availability' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}

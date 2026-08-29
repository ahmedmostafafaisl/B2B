<?php

namespace App\Http\Requests\EventType;

use App\Repositories\EventType\EventTypeRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventTypeId = $this->route('event_type');
        $eventTypeId = is_object($eventTypeId) ? $eventTypeId->id : $eventTypeId;

        return [
            'name'  => ['sometimes', 'string', 'max:255', Rule::unique('event_types', 'name')->ignore($eventTypeId)],
            'label' => ['sometimes', 'string', 'max:255'],
            'status' => ['nullable', 'in:' . implode(',', EventTypeRepository::VALID_STATUSES)],
            'requires_approval'    => ['nullable', 'boolean'],
            'affects_availability' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}

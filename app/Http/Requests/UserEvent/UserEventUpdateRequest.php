<?php

namespace App\Http\Requests\UserEvent;

use App\Repositories\UserEvent\UserEventRepository;
use Illuminate\Foundation\Http\FormRequest;

class UserEventUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['sometimes', 'integer', 'exists:users,id'],
            'event_type_id' => ['sometimes', 'integer', 'exists:event_types,id'],
            'status'  => ['nullable', 'in:' . implode(',', UserEventRepository::VALID_STATUSES)],
            'note'    => ['nullable', 'string'],

            'start_at' => ['nullable', 'date'],
            'end_at'   => ['nullable', 'date', 'after_or_equal:start_at'],

            'days_of_week'   => ['nullable', 'array'],
            'days_of_week.*' => ['string', 'in:' . implode(',', UserEventRepository::WEEKDAYS)],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time'   => ['nullable', 'date_format:H:i', 'required_with:start_time', 'after:start_time'],
        ];
    }
}

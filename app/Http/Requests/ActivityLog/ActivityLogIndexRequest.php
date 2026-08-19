<?php

namespace App\Http\Requests\ActivityLog;

use App\Repositories\ActivityLog\ActivityLogRepository;
use Illuminate\Foundation\Http\FormRequest;

class ActivityLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Short, whitelisted key — e.g. "contact" — not a raw class
            // string, so callers can't probe/target arbitrary models.
            'loggable_type' => ['required', 'string', 'in:' . implode(',', array_keys(ActivityLogRepository::LOGGABLE_TYPES))],
            'loggable_id'   => ['required', 'integer', 'min:1'],
            'action'        => ['nullable', 'string', 'max:255'],
            'per_page'      => ['nullable', 'integer', 'min:1', 'max:100'],
            'currentPage'   => ['nullable', 'integer', 'min:1'],
        ];
    }
}

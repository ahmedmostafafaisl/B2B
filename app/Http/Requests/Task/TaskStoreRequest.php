<?php

namespace App\Http\Requests\Task;

use App\Repositories\Task\TaskRepository;
use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'in:' . implode(',', TaskRepository::VALID_STATUSES)],
            'priority'    => ['nullable', 'in:' . implode(',', TaskRepository::VALID_PRIORITIES)],
            'due_date'    => ['nullable', 'date'],
            'contact_id'  => ['nullable', 'exists:contacts,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            // created_by is intentionally not accepted here — it is set
            // server-side from the authenticated user in TaskRepository.
        ];
    }
}

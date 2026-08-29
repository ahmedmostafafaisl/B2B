<?php

namespace App\Http\Requests\Role;

use App\Repositories\Role\RoleRepository;
use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'unique:roles,name'],
            'status' => ['nullable', 'in:' . implode(',', RoleRepository::VALID_STATUSES)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}

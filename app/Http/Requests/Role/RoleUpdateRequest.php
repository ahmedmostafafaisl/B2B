<?php

namespace App\Http\Requests\Role;

use App\Repositories\Role\RoleRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');
        $roleId = is_object($roleId) ? $roleId->id : $roleId;

        return [
            'name' => ['sometimes', 'string', Rule::unique('roles', 'name')->ignore($roleId)],
            'status' => ['nullable', 'in:' . implode(',', RoleRepository::VALID_STATUSES)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}

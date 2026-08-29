<?php

namespace App\Repositories\Role;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Http\Request;

class RoleRepository implements RoleRepositoryInterface
{
    public const VALID_STATUSES = [
        'active',
        'inactive',
    ];

    public function index(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $query = Role::query()
            ->with('permissions')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ];
    }

    public function findOrFail(int $id): Role
    {
        return Role::query()
            ->with('permissions')
            ->findOrFail($id);
    }

    public function store(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
            'status' => $data['status'] ?? 'active',
        ]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        $role->fill([
            'name' => $data['name'] ?? $role->name,
            'status' => $data['status'] ?? $role->status,
        ]);

        $becameInactive = $role->isDirty('status') && $role->status === 'inactive';

        $role->save();

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        if ($becameInactive) {
            $this->deactivateUsersWithRole($role);
        }

        return $role->loadCount('users')->load('permissions');
    }

    public function delete(Role $role): void
    {
        $this->deactivateUsersWithRole($role);
        $role->delete();
    }

    /**
     * Sets status='inactive' on every user currently holding this role.
     * A single bulk UPDATE rather than looping — used both when a role is
     * switched to inactive and right before a role is deleted.
     */
    private function deactivateUsersWithRole(Role $role): void
    {
        User::role($role->name)->update(['status' => 'inactive']);
    }
}

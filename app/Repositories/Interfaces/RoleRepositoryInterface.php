<?php

namespace App\Repositories\Interfaces;

use App\Models\Role;
use Illuminate\Http\Request;

interface RoleRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): Role;

    public function store(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): void;
}

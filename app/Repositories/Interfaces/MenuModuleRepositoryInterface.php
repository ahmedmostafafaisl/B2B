<?php

namespace App\Repositories\Interfaces;

use App\Models\MenuModule;
use Illuminate\Http\Request;

interface MenuModuleRepositoryInterface
{
    public function index(Request $request): array;
    public function findOrFail(int $id): MenuModule;
    public function store(array $data): MenuModule;
    public function update(MenuModule $module, array $data): MenuModule;
    public function delete(MenuModule $module): void;
}

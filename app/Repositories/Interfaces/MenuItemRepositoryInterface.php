<?php

namespace App\Repositories\Interfaces;

use App\Models\MenuItem;
use Illuminate\Http\Request;

interface MenuItemRepositoryInterface
{
    public function index(Request $request): array;
    public function findOrFail(int $id): MenuItem;
    public function store(array $data): MenuItem;
    public function update(MenuItem $item, array $data): MenuItem;
    public function delete(MenuItem $item): void;
}

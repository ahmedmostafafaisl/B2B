<?php

namespace App\Repositories\Interfaces;

use App\Models\Key;
use Illuminate\Http\Request;

interface KeyRepositoryInterface
{
    public function index(Request $request): array;
    public function findOrFail(int $id): Key;

    public function store(array $data): Key;
    public function update(Key $key, array $data): Key;
    public function delete(Key $key): void;
}

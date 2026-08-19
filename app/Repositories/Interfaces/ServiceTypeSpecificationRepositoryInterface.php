<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface ServiceTypeSpecificationRepositoryInterface
{
    public function index(Request $request): array;

    public function show(int $id): object;

    public function store(array $data): object;

    public function update(int $id, array $data): object;

    public function destroy(int $id): bool;
}

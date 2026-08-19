<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface TaskRepositoryInterface
{
    public function index(Request $request): JsonResponse|array;

    public function findOrFail(int $id): Task;

    public function store(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): void;
}

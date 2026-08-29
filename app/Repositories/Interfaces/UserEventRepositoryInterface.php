<?php

namespace App\Repositories\Interfaces;

use App\Models\UserEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface UserEventRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): UserEvent;

    /**
     * Fans out into one UserEvent row per id in data['user_ids'].
     */
    public function store(array $data): Collection;

    public function update(UserEvent $userEvent, array $data): UserEvent;

    public function delete(UserEvent $userEvent): void;
}

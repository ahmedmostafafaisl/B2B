<?php

namespace App\Repositories\Interfaces;

use App\Models\EventType;
use Illuminate\Http\Request;

interface EventTypeRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): EventType;

    public function store(array $data): EventType;

    public function update(EventType $eventType, array $data): EventType;

    public function delete(EventType $eventType): void;
}

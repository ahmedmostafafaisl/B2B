<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BannerRepositoryInterface
{
    public function index(Model $bannerable, Request $request): array;

    public function store(Model $bannerable, array $data);

    public function update(int $id, array $data);

    public function find(int $id);

    public function delete(int $id): void;
}

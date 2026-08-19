<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface SidebarRepositoryInterface
{
    public function sidebar(Request $request): array;

    public function getAllActive(string $modelClass, array $relations = [], array $filters = [], array $columns = ['*']): Collection;

    public function getManyActive(array $models): array;
}

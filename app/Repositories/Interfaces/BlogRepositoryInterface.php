<?php

namespace App\Repositories\Interfaces;

use App\Models\Blog;
use Illuminate\Http\Request;

interface BlogRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): Blog;

    public function store(array $data): Blog;

    public function update(Blog $blog, array $data): Blog;

    public function delete(Blog $blog): void;
}

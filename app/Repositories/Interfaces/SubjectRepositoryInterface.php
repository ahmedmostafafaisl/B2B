<?php

namespace App\Repositories\Interfaces;

use App\Models\Subject;
use Illuminate\Http\Request;

interface SubjectRepositoryInterface
{
    public function index(Request $request): array;
    public function findOrFail(int $id): Subject;

    public function store(array $data): Subject;
    public function update(Subject $subject, array $data): Subject;
    public function delete(Subject $subject): void;
}

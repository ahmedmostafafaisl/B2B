<?php

namespace App\Repositories\Interfaces;

use App\Models\Solution;
use Illuminate\Http\Request;

interface SolutionRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): Solution;

    public function store(array $data, array $images = [], ?int $primaryNewIndex = null): Solution;

    public function update(
        Solution $solution,
        array $data,
        array $newImages = [],
        array $deletedImageIds = [],
        ?int $primaryImageId = null,
        ?int $primaryNewIndex = null
    ): Solution;

    public function delete(Solution $solution): void;
}

<?php

namespace App\Repositories\Interfaces;

use App\Models\SubService;
use Illuminate\Http\Request;

interface SubServiceRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): SubService;

    public function store(array $data, array $images = [], $primaryImage = null, $image_365 = null): SubService;

    public function update(
        SubService $subService,
        array $data,
        $primaryImage = null,
        array $newImages = [],
        array $deletedImageIds = [],
        $image_365 = null
    ): SubService;

    public function delete(SubService $subService): void;
}

<?php

namespace App\Repositories\Interfaces;

use App\Models\Service;
use Illuminate\Http\Request;

interface ServiceRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): Service;

    public function store(array $data, array $images = [], $primaryImage = null, ?int $primaryNewIndex = null): Service;

    public function update(Service $service, array $data, array $newImages = [], array $deletedImageIds = [], ?int $primaryImageId = null, $primaryImage = null, ?int $primaryNewIndex = null): Service;

    public function delete(Service $service): void;
}

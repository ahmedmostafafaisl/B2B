<?php

namespace App\Repositories\Interfaces;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

interface ServiceTypeRepositoryInterface
{
    public function index(Request $request): array;

    public function findOrFail(int $id): ServiceType;

    public function store(array $data, ?UploadedFile $primaryImage = null): ServiceType;

    public function update(ServiceType $serviceType, array $data, ?UploadedFile $primaryImage = null): ServiceType;

    public function delete(ServiceType $serviceType): void;
}

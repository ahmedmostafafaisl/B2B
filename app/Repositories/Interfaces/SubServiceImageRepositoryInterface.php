<?php

namespace App\Repositories\Interfaces;

use App\Models\SubService;
use App\Models\SubServiceImage;

interface SubServiceImageRepositoryInterface
{
    public function uploadForSubService(SubService $subService, array $images = [], ?int $primaryNewIndex = null): SubService;

    public function deleteImage(SubServiceImage $image): void;

    public function setPrimary(SubServiceImage $image): SubService;
}

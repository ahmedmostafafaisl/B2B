<?php

namespace App\Repositories\Interfaces;

use App\Models\Service;
use App\Models\ServiceImage;

interface ServiceImageRepositoryInterface
{

    public function uploadForService(Service $service, array $images = [], ?int $primaryNewIndex = null): Service;
    public function deleteImage(ServiceImage $image): void;
    public function setPrimary(ServiceImage $image): Service;
}

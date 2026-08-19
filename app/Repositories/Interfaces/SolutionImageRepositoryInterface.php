<?php

namespace App\Repositories\Interfaces;

use App\Models\Solution;
use App\Models\SolutionImage;

interface SolutionImageRepositoryInterface
{
    public function uploadForSolution(Solution $solution, array $images = [], ?int $primaryNewIndex = null): Solution;

    public function deleteImage(SolutionImage $image): void;

    public function setPrimary(SolutionImage $image): Solution;
}

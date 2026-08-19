<?php

namespace App\Repositories\Interfaces;

interface GlobalSearchRepositoryInterface
{
    public function search(string $query): array;
}

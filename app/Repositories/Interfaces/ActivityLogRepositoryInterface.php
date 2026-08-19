<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface ActivityLogRepositoryInterface
{
    public function index(Request $request): array;
}

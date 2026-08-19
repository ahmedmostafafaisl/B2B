<?php

namespace App\Repositories\Interfaces;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ContactRepositoryInterface
{
    public function index(Request $request): JsonResponse|array;

    public function findOrFail(int $id): Contact;

    public function store(array $data): Contact;

    public function update(Contact $contact, array $data): Contact;

    public function delete(Contact $contact): void;
}

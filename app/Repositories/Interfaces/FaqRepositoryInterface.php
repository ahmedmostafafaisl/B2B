<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface FaqRepositoryInterface
{
    public function index(Model $faqable, Request $request): array;
    public function store(Model $faqable, array $data);
    public function update(int $id, array $data);
    public function bulkUpdate(Model $faqable, array $faqs): array;
    public function bulkReplace(Model $faqable, array $faqs): array;
    public function find(int $id);
    public function delete(int $id): void;

    public function bulkStore(Model $faqable, array $faqs): array;

}

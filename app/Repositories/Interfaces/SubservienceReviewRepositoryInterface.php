<?php

namespace App\Repositories\Interfaces;

use App\Models\SubservienceReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubservienceReviewRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): SubservienceReview;

    public function create(array $data): SubservienceReview;

    public function update(SubservienceReview $review, array $data): SubservienceReview;

    public function delete(SubservienceReview $review): void;
}

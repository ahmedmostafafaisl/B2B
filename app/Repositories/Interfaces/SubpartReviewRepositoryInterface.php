<?php

namespace App\Repositories\Interfaces;

use App\Models\SubpartReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubpartReviewRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function findOrFail(int $id): SubpartReview;

    public function create(array $data): SubpartReview;

    public function update(SubpartReview $review, array $data): SubpartReview;

    public function delete(SubpartReview $review): void;
}

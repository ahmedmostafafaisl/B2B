<?php

namespace App\Repositories\SubservienceReview;

use App\Models\SubservienceReview;
use App\Repositories\Interfaces\SubservienceReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubservienceReviewRepository implements SubservienceReviewRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return SubservienceReview::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubservienceReview
    {
        return SubservienceReview::query()->findOrFail($id);
    }

    public function create(array $data): SubservienceReview
    {
        return SubservienceReview::query()->create($data);
    }

    public function update(SubservienceReview $review, array $data): SubservienceReview
    {
        $review->update($data);
        return $review->fresh();
    }

    public function delete(SubservienceReview $review): void
    {
        $review->delete();
    }
}

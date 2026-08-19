<?php

namespace App\Repositories\SubpartReview;

use App\Models\SubpartReview;
use App\Repositories\Interfaces\SubpartReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubpartReviewRepository implements SubpartReviewRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return SubpartReview::query()
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubpartReview
    {
        return SubpartReview::query()->findOrFail($id);
    }

    public function create(array $data): SubpartReview
    {
        return SubpartReview::query()->create($data);
    }

    public function update(SubpartReview $review, array $data): SubpartReview
    {
        $review->update($data);
        return $review->fresh();
    }

    public function delete(SubpartReview $review): void
    {
        $review->delete();
    }
}

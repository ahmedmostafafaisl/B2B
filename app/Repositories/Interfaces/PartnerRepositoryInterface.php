<?php

namespace App\Repositories\Interfaces;

use App\Models\Partner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface PartnerRepositoryInterface
{
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    public function findOrFail(int $id): Partner;

    public function create(array $data, ?UploadedFile $logo = null): Partner;

    public function update(Partner $partner, array $data, ?UploadedFile $logo = null): Partner;

    public function delete(Partner $partner): void;

    public function bulk(array $rows, array $logos = []): \Illuminate\Support\Collection;
}

<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\BulkPartnerRequest;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\Partner\PartnerResource;
use App\Repositories\Interfaces\PartnerRepositoryInterface;
use App\Traits\ApiPaginationResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ApiPaginationResponse;

    public function __construct(
        private readonly PartnerRepositoryInterface $repo
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $filters = $request->only(['is_active']);
        $paginator = $this->repo->paginate($perPage, $filters);

        return $this->paginatedResponse(
            $paginator,
            PartnerResource::collection($paginator->items())
        );
    }

    public function show(int $id)
    {
        return new PartnerResource($this->repo->findOrFail($id));
    }

    public function store(StorePartnerRequest $request)
    {
        $partner = $this->repo->create(
            $request->validated(),
            $request->file('logo')
        );

        return (new PartnerResource($partner))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePartnerRequest $request, int $id)
    {
        $partner = $this->repo->findOrFail($id);

        $updated = $this->repo->update(
            $partner,
            $request->validated(),
            $request->file('logo')
        );

        return new PartnerResource($updated);
    }

    public function destroy(int $id)
    {
        $this->repo->delete($this->repo->findOrFail($id));

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function bulk(BulkPartnerRequest $request)
    {
        $logos = [];
        foreach ($request->validated()['partners'] as $index => $row) {
            $logos[$index] = $request->file("partners.{$index}.logo");
        }

        $partners = $this->repo->bulk($request->validated()['partners'], $logos);

        return response()->json([
            'message' => "{$partners->count()} partners processed successfully.",
            'partners' => PartnerResource::collection($partners),
        ]);
    }
}

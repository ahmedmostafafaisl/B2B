<?php

namespace App\Http\Controllers\Api\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqBulkReplaceRequest;
use App\Http\Requests\Faq\FaqBulkStoreRequest;
use App\Http\Requests\Faq\FaqBulkUpdateRequest;
use App\Http\Requests\Faq\FaqStoreRequest;
use App\Http\Requests\FaqUpdateRequest;
use App\Http\Resources\Faq\FaqResource;
use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SubPart;
use App\Models\SubService;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(private FaqRepositoryInterface $faqs) {}

    public function index(string $type, int $id, Request $request)
    {
        $faqable = $this->resolveFaqable($type, $id);
        $result = $this->faqs->index($faqable, $request);

        return response()->json([
            'items' => FaqResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(string $type, int $id, FaqStoreRequest $request)
    {
        $faqable = $this->resolveFaqable($type, $id);
        $faq = $this->faqs->store($faqable, $request->validated());

        return (new FaqResource($faq))->response()->setStatusCode(201);
    }

    public function bulkStore(string $type, int $id, FaqBulkStoreRequest $request)
    {
        $faqable = $this->resolveFaqable($type, $id);

        $created = $this->faqs->bulkStore($faqable, $request->validated()['faqs']);

        return response()->json([
            'message' => ($type).' FAQs saved successfully.',
            'data' => [
                'id' => $id,
                'faqs' => FaqResource::collection(collect($created)),
            ],
        ], 201);
    }

    public function update(FaqUpdateRequest $request, int $id)
    {
        $faq = $this->faqs->update($id, $request->validated());

        return new FaqResource($faq);
    }

    public function bulkUpdate(string $type, int $id, FaqBulkUpdateRequest $request)
    {
        $faqable = $this->resolveFaqable($type, $id);

        $updatedList = $this->faqs->bulkUpdate($faqable, $request->validated()['faqs']);

        return response()->json([
            'items' => FaqResource::collection(collect($updatedList)),
            'message' => 'FAQs updated successfully',
        ]);
    }

    public function bulkReplace(string $type, int $id, FaqBulkReplaceRequest $request)
    {
        $faqable = $this->resolveFaqable($type, $id);

        $list = $this->faqs->bulkReplace($faqable, $request->validated()['faqs']);

        return response()->json([
            'items' => FaqResource::collection(collect($list)),
            'message' => 'FAQs replaced successfully',
        ]);
    }

    public function destroy(int $id)
    {
        $this->faqs->delete($id);

        return response()->json(['message' => 'FAQ deleted successfully']);
    }

    public function show(int $id)
    {
        $faq = $this->faqs->find($id);

        return new FaqResource($faq);
    }

    private function resolveFaqable(string $type, int $id)
    {
        return match ($type) {
            'services' => Service::findOrFail($id),
            'service-types' => ServiceType::findOrFail($id),
            'sub-services' => SubService::findOrFail($id),
            'parts' => Part::findOrFail($id),
            'sub-parts' => SubPart::findOrFail($id),
            default => abort(404),
        };
    }
}

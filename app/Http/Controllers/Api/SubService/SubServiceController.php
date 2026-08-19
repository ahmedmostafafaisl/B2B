<?php

namespace App\Http\Controllers\Api\SubService;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubService\SubServiceStoreRequest;
use App\Http\Requests\SubService\SubServiceUpdateRequest;
use App\Http\Resources\SubService\SubServiceResource;
use App\Models\SubServiceApplication;
use App\Models\SubServiceFeature;
use App\Models\SubServiceModel;
use App\Models\SubservienceDoc;
use App\Models\SubservienceReview;
use App\Models\SubservienceSpecification;
use App\Repositories\Interfaces\SubServiceRepositoryInterface;
use App\Traits\SubServiceModelFetcher;
use Illuminate\Http\Request;

class SubServiceController extends Controller
{
    use SubServiceModelFetcher;

    public function __construct(private SubServiceRepositoryInterface $subServices) {}

    public function index(Request $request)
    {
        $result = $this->subServices->index($request);

        return response()->json([
            'items' => SubServiceResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(SubServiceStoreRequest $request)
    {
        $validated = $request->validated();

        $images = $request->file('images', []);
        $primaryImage = $request->file('primary_image', null);
        $image_365 = $request->file('image_365', null);

        unset($validated['images'], $validated['primary_image'], $validated['image_365']);

        $subService = $this->subServices->store($validated, $images, $primaryImage, $image_365);

        return (new SubServiceResource($subService))->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        $subService = $this->subServices->findOrFail($id);

        return new SubServiceResource($subService);
    }

    public function update(SubServiceUpdateRequest $request, int $id)
    {
        $subService = $this->subServices->findOrFail($id);

        $validated = $request->validated();

        $newImages = $request->file('images', []);
        $deletedImageIds = $validated['deleted_image_ids'] ?? [];
        $primaryImage = $request->file('primary_image', null);
        $image_365 = $request->file('image_365', null);

        unset(
            $validated['images'],
            $validated['deleted_image_ids'],
            $validated['primary_image_id'],
            $validated['primary_new_index'],
            $validated['primary_image'],
            $validated['image_365'],
        );

        $updated = $this->subServices->update(
            $subService,
            $validated,
            $primaryImage,
            $newImages,
            $deletedImageIds,
            $image_365,

        );

        return new SubServiceResource($updated);
    }

    public function destroy(int $id)
    {
        $subService = $this->subServices->findOrFail($id);
        $this->subServices->delete($subService);

        return response()->json(['message' => 'Sub service deleted successfully']);
    }

    private array $map = [
        'features' => SubServiceFeature::class,
        'models' => SubServiceModel::class,
        'applications' => SubServiceApplication::class,
        'specifications' => SubservienceSpecification::class,
        'reviews' => SubservienceReview::class,
        'docs' => SubservienceDoc::class,
        // add your other 3 models here
    ];

    public function showByModule($id, $module)
    {
        if (! isset($this->map[$module])) {
            return response()->json(['message' => 'Invalid module'], 422);
        }

        return $this->getBySubServiceId((int) $id, $this->map[$module]);
    }
}

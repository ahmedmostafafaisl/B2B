<?php

namespace App\Http\Controllers\Api\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\BannerStoreRequest;
use App\Http\Requests\Banner\BannerUpdateRequest;
use App\Http\Resources\Banner\BannerResource;
use App\Models\Part;
use App\Models\SubPart;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(private BannerRepositoryInterface $banners) {}

    public function index(string $type, int $id, Request $request)
    {
        $bannerable = $this->resolveBannerable($type, $id);
        $result = $this->banners->index($bannerable, $request);

        return response()->json([
            'items' => BannerResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(string $type, int $id, BannerStoreRequest $request)
    {
        $bannerable = $this->resolveBannerable($type, $id);

        $data = $request->validated();
        $data['_image_file'] = $request->file('image');

        $banner = $this->banners->store($bannerable, $data);

        return (new BannerResource($banner))->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        $banner = $this->banners->find($id);

        return new BannerResource($banner);
    }

    public function update(BannerUpdateRequest $request, int $id)
    {
        $data = $request->validated();
        $data['_image_file'] = $request->file('image');
        $data['_remove_image'] = $request->boolean('remove_image');

        $banner = $this->banners->update($id, $data);

        return new BannerResource($banner);
    }

    public function destroy(int $id)
    {
        $this->banners->delete($id);

        return response()->json(['message' => 'Banner deleted successfully']);
    }

    private function resolveBannerable(string $type, int $id)
    {
        return match ($type) {
            'parts' => Part::findOrFail($id),
            'sub-parts' => SubPart::findOrFail($id),
            default => abort(404),
        };
    }
}

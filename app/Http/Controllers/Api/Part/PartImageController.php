<?php

namespace App\Http\Controllers\Api\Part;

use App\Http\Controllers\Controller;
use App\Http\Requests\Part\PartImagesUploadRequest;
use App\Http\Resources\Part\PartResource;
use App\Models\Part;
use App\Models\PartImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PartImageController extends Controller
{
    /**
     * POST /parts/{id}/images
     */
    public function upload(PartImagesUploadRequest $request, int $id)
    {
        $part = Part::query()->findOrFail($id);

        $images = $request->file('images', []);
        $primaryNewIndex = $request->input('primary_new_index');

        $part = DB::transaction(function () use ($part, $images, $primaryNewIndex) {
            $created = [];

            foreach ($images as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->store('parts', 's3');

                $created[] = PartImage::create([
                    'part_id' => $part->id,
                    'image' => $path,
                    'is_primary' => false,
                ]);
            }

            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                PartImage::query()->where('part_id', $part->id)->update(['is_primary' => false]);
                $created[$primaryNewIndex]->update(['is_primary' => true]);
            } else {
                $hasPrimary = PartImage::query()->where('part_id', $part->id)->where('is_primary', true)->exists();
                if (! $hasPrimary && ! empty($created)) {
                    PartImage::query()->where('part_id', $part->id)->update(['is_primary' => false]);
                    $created[0]->update(['is_primary' => true]);
                }
            }

            return $part->refresh()->load(['images', 'primaryImage']);
        });

        return new PartResource($part);
    }

    /**
     * DELETE /parts/images/{imageId}
     */
    public function destroy(int $imageId)
    {
        $image = PartImage::query()->findOrFail($imageId);

        DB::transaction(function () use ($image) {
            $partId = $image->part_id;
            $wasPrimary = (bool) $image->is_primary;

            if ($image->image && Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }

            $image->delete();

            if ($wasPrimary) {
                $next = PartImage::query()->where('part_id', $partId)->orderBy('id')->first();
                if ($next) {
                    PartImage::query()->where('part_id', $partId)->update(['is_primary' => false]);
                    $next->update(['is_primary' => true]);
                }
            }
        });

        return response()->json(['message' => 'Image deleted successfully']);
    }

    /**
     * PATCH /parts/images/{imageId}/primary
     */
    public function setPrimary(int $imageId)
    {
        $image = PartImage::query()->findOrFail($imageId);

        $part = DB::transaction(function () use ($image) {
            PartImage::query()
                ->where('part_id', $image->part_id)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);

            return $image->part()->firstOrFail()->load(['images', 'primaryImage']);
        });

        return new PartResource($part);
    }
}

<?php

namespace App\Repositories\Solution;

use App\Models\Solution;
use App\Models\SolutionImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Interfaces\SolutionImageRepositoryInterface;

class SolutionImageRepository implements SolutionImageRepositoryInterface
{
    public function uploadForSolution(Solution $solution, array $images = [], ?int $primaryNewIndex = null): Solution
    {
        return DB::transaction(function () use ($solution, $images, $primaryNewIndex) {
            $created = [];

            foreach ($images as $file) {
                if (!$file instanceof UploadedFile) continue;

                $path = $file->store('solutions', 's3');

                $created[] = SolutionImage::create([
                    'solution_id' => $solution->id,
                    'image' => $path,
                    'is_primary' => false,
                ]);
            }

            if ($primaryNewIndex !== null && isset($created[$primaryNewIndex])) {
                $this->setPrimary($created[$primaryNewIndex]);
            } else {
                $hasPrimary = SolutionImage::query()
                    ->where('solution_id', $solution->id)
                    ->where('is_primary', true)
                    ->exists();

                if (!$hasPrimary && !empty($created)) {
                    $this->setPrimary($created[0]);
                }
            }

            return $solution->refresh()->load(['images', 'primaryImage']);
        });
    }

    public function deleteImage(SolutionImage $image): void
    {
        DB::transaction(function () use ($image) {
            $solutionId = $image->solution_id;
            $wasPrimary = (bool) $image->is_primary;

            $this->deleteFileIfExists($image->image);
            $image->delete();

            if ($wasPrimary) {
                $next = SolutionImage::query()
                    ->where('solution_id', $solutionId)
                    ->orderBy('id')
                    ->first();

                if ($next) $this->setPrimary($next);
            }
        });
    }

    public function setPrimary(SolutionImage $image): Solution
    {
        return DB::transaction(function () use ($image) {
            SolutionImage::query()
                ->where('solution_id', $image->solution_id)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);

            return $image->solution()->firstOrFail()->load(['images', 'primaryImage']);
        });
    }

    private function deleteFileIfExists(?string $path): void
    {
        if (!$path) return;

        $disk = Storage::disk('public');
        if ($disk->exists($path)) $disk->delete($path);
    }
}

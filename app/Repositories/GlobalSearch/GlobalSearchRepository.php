<?php

namespace App\Repositories\GlobalSearch;

use App\Models\Blog;
use App\Models\Part;
use App\Models\Service;
use App\Models\Solution;
use App\Models\SubPart;
use App\Models\SubService;
use App\Repositories\Interfaces\GlobalSearchRepositoryInterface;

class GlobalSearchRepository implements GlobalSearchRepositoryInterface
{
    private int $limit = 10;

    // ── columns to search per model ───────────────────────────────────────────
    private array $searchableColumns = [
        Blog::class => ['title'],                  // no 'name' column
        Part::class => ['title'],                  // no 'name' column
        Service::class => ['title'],                  // no 'name' column
        Solution::class => ['title'],                  // no 'name' column
        SubPart::class => ['title'],                  // no 'name' column
        SubService::class => ['title'],                  // no 'name' column
    ];

    // ── image column per model ────────────────────────────────────────────────
    private array $imageColumn = [
        Blog::class => 'image',
        Part::class => 'primary_image',
        Service::class => 'primary_image',
        Solution::class => 'banner',
        SubPart::class => 'primary_image',
        SubService::class => 'primary_image',
    ];

    public function search(string $query): array
    {
        return [
            'services' => $this->searchModel(Service::class, $query, 'service'),
            'sub_services' => $this->searchModel(SubService::class, $query, 'sub_service'),
            'solutions' => $this->searchModel(Solution::class, $query, 'solution'),
            'blogs' => $this->searchModel(Blog::class, $query, 'blog'),
            'parts' => $this->searchModel(Part::class, $query, 'part'),
            'sub_parts' => $this->searchModel(SubPart::class, $query, 'sub_part'),
        ];
    }

    private function searchModel(string $model, string $query, string $type): \Illuminate\Support\Collection
    {
        $columns = $this->searchableColumns[$model] ?? ['title'];
        $imageCol = $this->imageColumn[$model] ?? null;

        // select only existing columns
        $select = array_filter(['id', 'title', 'slug', $imageCol], fn ($c) => ! is_null($c));

        return $model::query()
            ->where('is_active', true)
            ->where(function ($q) use ($query, $columns) {
                foreach ($columns as $index => $column) {
                    if ($index === 0) {
                        $q->where($column, 'like', "%{$query}%");
                    } else {
                        $q->orWhere($column, 'like', "%{$query}%");
                    }
                }
            })
            ->select($select)
            ->limit($this->limit)
            ->get()
            ->map(function ($item) use ($type, $imageCol) {
                $imageValue = $imageCol ? ($item->{$imageCol} ?? null) : null;

                // generate S3 url if model has accessor, otherwise use raw value
                $imageUrl = $item->primary_image_url
                    ?? ($imageValue ? \Illuminate\Support\Facades\Storage::disk('s3')->url($imageValue) : null);

                return [
                    'id' => $item->id,
                    'type' => $type,
                    'title' => $item->title ?? null,
                    'slug' => $item->slug ?? null,
                    'image' => $imageUrl,
                ];
            });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\SubPart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubPartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = Part::query()->pluck('id')->all();
        if (empty($parts)) {
            return;
        }

        foreach ($parts as $partId) {
            // Level 1 — root sub_parts (direct children of part)
            for ($i = 1; $i <= 4; $i++) {
                $title = "Sub Part {$i} for Part {$partId}";

                $parent = SubPart::query()->updateOrCreate(
                    [
                        'part_id' => $partId,
                        'parent_id' => null,
                        'slug' => Str::slug($title),
                    ],
                    [
                        'title' => $title,
                        'description' => "Description for {$title}",
                        'is_active' => true,
                        'sort_order' => $i,
                        'primary_image' => null,
                        'image_365' => null,
                        'description_365' => "365 description for {$title}",
                    ]
                );

                // Level 2 — children of each root sub_part
                for ($j = 1; $j <= 3; $j++) {
                    $childTitle = "Child {$j} of {$title}";

                    $child = SubPart::query()->updateOrCreate(
                        [
                            'part_id' => $partId,
                            'parent_id' => $parent->id,
                            'slug' => Str::slug($childTitle),
                        ],
                        [
                            'title' => $childTitle,
                            'description' => "Description for {$childTitle}",
                            'is_active' => true,
                            'sort_order' => $j,
                            'primary_image' => null,
                            'image_365' => null,
                            'description_365' => "365 description for {$childTitle}",
                        ]
                    );

                    // Level 3 — grandchildren
                    for ($k = 1; $k <= 2; $k++) {
                        $grandTitle = "Sub Child {$k} of {$childTitle}";

                        SubPart::query()->updateOrCreate(
                            [
                                'part_id' => $partId,
                                'parent_id' => $child->id,
                                'slug' => Str::slug($grandTitle),
                            ],
                            [
                                'title' => $grandTitle,
                                'description' => "Description for {$grandTitle}",
                                'is_active' => true,
                                'sort_order' => $k,
                                'primary_image' => null,
                                'image_365' => null,
                                'description_365' => "365 description for {$grandTitle}",
                            ]
                        );
                    }
                }
            }
        }
    }
}

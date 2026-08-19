<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\SubPart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        $groups = [
            [
                'model' => Part::class,
                'items' => Part::query()->select('id', 'title', 'description')->get(),
                'label' => fn ($item) => $item->title ?: $item->description ?: "Part {$item->id}",
            ],
            [
                'model' => SubPart::class,
                'items' => SubPart::query()->select('id', 'title', 'description')->get(),
                'label' => fn ($item) => $item->title ?: $item->description ?: "SubPart {$item->id}",
            ],
        ];

        foreach ($groups as $group) {
            $modelClass = $group['model'];
            $items = $group['items'];
            $labelResolver = $group['label'];

            foreach ($items as $item) {
                $name = $labelResolver($item);

                $banners = [
                    [
                        'title' => "{$name} Banner 1",
                        'description' => "Overview banner for {$name}, highlighting key information and visual presentation.",
                        'image' => null,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => "{$name} Banner 2",
                        'description' => "Secondary banner for {$name}, used for additional presentation and supporting content.",
                        'image' => null,
                        'sort_order' => 2,
                    ],
                ];

                foreach ($banners as $banner) {
                    $rows[] = [
                        'bannerable_type' => $modelClass,
                        'bannerable_id' => $item->id,
                        'title' => $banner['title'],
                        'description' => $banner['description'],
                        'image' => $banner['image'],
                        'sort_order' => $banner['sort_order'],
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            DB::table('banners')->insert($rows);
        }
    }
}

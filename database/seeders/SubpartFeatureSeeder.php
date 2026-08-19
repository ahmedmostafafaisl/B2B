<?php

namespace Database\Seeders;

use App\Models\SubPart;
use App\Models\SubpartFeature;
use App\Models\SubpartFeatureType;
use App\Models\SubpartFeatureItem;
use Illuminate\Database\Seeder;

class SubpartFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $subParts = SubPart::query()->pluck('id')->all();
        if (empty($subParts)) return;

        foreach ($subParts as $subPartId) {
            $feature = SubpartFeature::query()->updateOrCreate(
                [
                    'sub_part_id' => $subPartId,
                    'title' => 'Features',
                ],
                [
                    'image' => 'subparts/features/sample.jpg', // placeholder
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            // replace types/items
            $feature->types()->delete();

            $t1 = SubpartFeatureType::query()->create([
                'subpart_feature_id' => $feature->id,
                'name' => 'Standard Features',
                'sort_order' => 0,
            ]);

            foreach ([
                ['text' => 'High efficiency', 'sort_order' => 0],
                ['text' => 'Low maintenance', 'sort_order' => 1],
                ['text' => 'Compact design', 'sort_order' => 2],
            ] as $row) {
                SubpartFeatureItem::query()->create([
                    'subpart_feature_type_id' => $t1->id,
                    'text' => $row['text'],
                    'sort_order' => $row['sort_order'],
                ]);
            }

            $t2 = SubpartFeatureType::query()->create([
                'subpart_feature_id' => $feature->id,
                'name' => 'Available Options',
                'sort_order' => 1,
            ]);

            foreach ([
                ['text' => 'Stainless steel frame', 'sort_order' => 0],
                ['text' => 'Extra filtration stage', 'sort_order' => 1],
            ] as $row) {
                SubpartFeatureItem::query()->create([
                    'subpart_feature_type_id' => $t2->id,
                    'text' => $row['text'],
                    'sort_order' => $row['sort_order'],
                ]);
            }
        }
    }
}

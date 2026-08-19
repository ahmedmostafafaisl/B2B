<?php

namespace Database\Seeders;

use App\Models\SubPart;
use App\Models\SubpartModel;
use App\Models\SubpartModelSection;
use App\Models\SubpartModelItem;
use Illuminate\Database\Seeder;

class SubpartModelSeeder extends Seeder
{
    public function run(): void
    {
        $subParts = SubPart::query()->pluck('id')->all();
        if (empty($subParts)) return;

        foreach ($subParts as $subPartId) {
            $model = SubpartModel::query()->updateOrCreate(
                ['sub_part_id' => $subPartId, 'title' => 'Models'],
                [
                    'image' => 'subparts/models/sample.jpg', // placeholder
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            $model->sections()->delete();

            $s1 = SubpartModelSection::query()->create([
                'subpart_model_id' => $model->id,
                'title' => 'Operation Specifics',
                'sort_order' => 0,
            ]);

            foreach ([
                ['text' => 'Operating pressure up to 10 bar', 'sort_order' => 0],
                ['text' => 'Temperature up to 45°C', 'sort_order' => 1],
            ] as $row) {
                SubpartModelItem::query()->create([
                    'subpart_model_section_id' => $s1->id,
                    'text' => $row['text'],
                    'sort_order' => $row['sort_order'],
                ]);
            }

            $s2 = SubpartModelSection::query()->create([
                'subpart_model_id' => $model->id,
                'title' => 'Materials of Construction',
                'sort_order' => 1,
            ]);

            foreach ([
                ['text' => 'Stainless steel', 'sort_order' => 0],
                ['text' => 'FRP', 'sort_order' => 1],
            ] as $row) {
                SubpartModelItem::query()->create([
                    'subpart_model_section_id' => $s2->id,
                    'text' => $row['text'],
                    'sort_order' => $row['sort_order'],
                ]);
            }
        }
    }
}

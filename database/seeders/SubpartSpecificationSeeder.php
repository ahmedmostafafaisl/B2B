<?php

namespace Database\Seeders;

use App\Models\SubPart;
use App\Models\SubpartSpecification;
use Illuminate\Database\Seeder;

class SubpartSpecificationSeeder extends Seeder
{
    public function run(): void
    {
        $subParts = SubPart::query()->pluck('id')->all();
        if (empty($subParts)) return;

        foreach ($subParts as $subPartId) {
            $rows = [
                ['type' => 'technical', 'title' => 'Max Pressure', 'description' => 'Up to 10 bar'],
                ['type' => 'technical', 'title' => 'Max Temperature', 'description' => 'Up to 45°C'],
                ['type' => 'dimension', 'title' => 'Size', 'description' => 'Standard'],
                ['type' => 'material', 'title' => 'Material', 'description' => 'Stainless Steel / FRP'],
            ];

            foreach ($rows as $r) {
                SubpartSpecification::query()->updateOrCreate(
                    [
                        'sub_part_id' => $subPartId,
                        'type' => $r['type'],
                        'title' => $r['title'],
                    ],
                    [
                        'description' => $r['description'],
                    ]
                );
            }
        }
    }
}

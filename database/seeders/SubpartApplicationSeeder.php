<?php

namespace Database\Seeders;

use App\Models\SubPart;
use App\Models\SubpartApplication;
use Illuminate\Database\Seeder;

class SubpartApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $subParts = SubPart::query()->pluck('id')->all();
        if (empty($subParts)) return;

        foreach ($subParts as $subPartId) {
            SubpartApplication::query()->updateOrCreate(
                [
                    'sub_part_id' => $subPartId,
                    'title' => 'Applications',
                ],
                [
                    'items' => [
                        'Industrial water treatment',
                        'Food & beverage processing',
                        ['text' => 'Pharmaceutical plants', 'icon' => 'check'],
                    ],
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}

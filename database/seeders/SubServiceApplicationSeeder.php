<?php

namespace Database\Seeders;

use App\Models\SubServiceApplication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServiceApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $subServices = DB::table('sub_services')->select('id', 'title')->get();

        if ($subServices->isEmpty()) {
            return;
        }

        $defaultItems = [
            'Municipal drinking water',
            'Industrial process water',
            'Cooling towers',
            'Wastewater reuse',
            'Pretreatment systems',
            'Food & beverage process water',
        ];

        foreach ($subServices as $ss) {
            // Avoid duplicates: one Applications per sub_service
            SubServiceApplication::query()->updateOrCreate(
                [
                    'sub_service_id' => $ss->id,
                    'title' => 'Applications',
                ],
                [
                    'items' => $defaultItems,
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}

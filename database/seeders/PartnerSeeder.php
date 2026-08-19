<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            Partner::query()->updateOrCreate(
                ['name' => "Partner {$i}"],
                [
                    'is_active' => true,
                    'sort_order' => $i,
                    'logo' => null,
                ]
            );
        }
    }
}

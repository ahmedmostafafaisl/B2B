<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            'Membrane',
            'Pump',
            'Valve',
            'Filter Housing',
            'Pressure Vessel',
            'Instrumentation',
        ];

        foreach ($titles as $i => $title) {
            Part::query()->updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'subtitle' => 'Subtitle for ' . $title,
                    'description' => 'Description for ' . $title,
                    'is_active' => true,
                    'sort_order' => $i,
                    'primary_image' => null, // upload later
                ]
            );
        }
    }
}

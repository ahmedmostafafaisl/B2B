<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $firstService = Service::query()->first();

        if (! $firstService) {
            return;
        }

        $items = [
            [
                'service_id' => $firstService->id,
                'code' => 'water-treatment',
                'name' => 'Water Treatment',
                'title' => 'Water Treatment Systems',
                'subtitle' => 'Clean and safe water solutions',
                'description' => 'Advanced water treatment systems for industrial and commercial applications.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'service_id' => $firstService->id,
                'code' => 'ro-systems',
                'name' => 'RO Systems',
                'title' => 'RO Membrane Systems',
                'subtitle' => 'High-performance reverse osmosis solutions',
                'description' => 'Reverse osmosis membrane systems for high-efficiency purification.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'service_id' => $firstService->id,
                'code' => 'industrial-filtration',
                'name' => 'Industrial Filtration',
                'title' => 'Industrial Filtration',
                'subtitle' => 'Efficient filtration for industrial applications',
                'description' => 'Industrial filtration solutions tailored for heavy-duty usage.',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($items as $item) {
            ServiceType::create($item);
        }
    }
}

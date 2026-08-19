<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Water Treatment Systems',
                'description' => 'Advanced water treatment solutions',
                'subtitle' => 'Clean and safe water for all applications',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'RO Membrane Systems',
                'subtitle' => 'High-performance reverse osmosis solutions',
                'description' => 'Reverse osmosis membrane systems',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Industrial Filtration',
                'subtitle' => 'Efficient filtration for industrial applications',
                'description' => 'Industrial filtration services',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                'title' => $service['title'],
                'subtitle' => $service['subtitle'],
                'slug' => Str::slug($service['title']),
                'description' => $service['description'],
                'is_active' => $service['is_active'],
                'sort_order' => $service['sort_order'],
            ]);
        }
    }
}

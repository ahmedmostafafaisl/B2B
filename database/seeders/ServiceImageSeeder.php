<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Database\Seeder;

class ServiceImageSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();

        foreach ($services as $service) {

            $images = [
                "services/demo_{$service->id}_1.jpg",
                "services/demo_{$service->id}_2.jpg",
                "services/demo_{$service->id}_3.jpg",
            ];

            foreach ($images as $index => $img) {
                ServiceImage::create([
                    'service_id' => $service->id,
                    'image' => $img,
                    'is_primary' => $index === 0,
                ]);
            }
        }
    }
}

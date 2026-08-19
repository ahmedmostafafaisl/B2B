<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Get all service types
        $serviceTypes = DB::table('service_types')
            ->select('id', 'title')
            ->get();

        if ($serviceTypes->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($serviceTypes as $serviceType) {
            for ($i = 1; $i <= 4; $i++) {
                $title = "{$serviceType->title} - Sub Service {$i}";

                $rows[] = [
                    'service_type_id' => $serviceType->id,
                    'title' => $title,
                    'slug' => Str::slug($title),
                    'description' => "Description for {$title}",
                    'is_active' => true,
                    'sort_order' => $i,
                    'primary_image' => null,
                    'image_365' => null,
                    'description_365' => "Description for {$title}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Optional: avoid duplicates by clearing existing sub_services first
        // DB::table('sub_services')->truncate();

        DB::table('sub_services')->insert($rows);
    }
}

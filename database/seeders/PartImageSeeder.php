<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\PartImage;
use Illuminate\Database\Seeder;

class PartImageSeeder extends Seeder
{
    public function run(): void
    {
        $parts = Part::all();

        foreach ($parts as $part) {

            $images = [
                "parts/demo_{$part->id}_1.jpg",
                "parts/demo_{$part->id}_2.jpg",
                "parts/demo_{$part->id}_3.jpg",
            ];

            foreach ($images as $index => $img) {
                PartImage::create([
                    'part_id' => $part->id,
                    'image' => $img,
                    'is_primary' => $index === 0,
                ]);
            }
        }
    }
}

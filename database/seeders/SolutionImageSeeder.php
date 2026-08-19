<?php

namespace Database\Seeders;

use App\Models\Solution;
use App\Models\SolutionImage;
use Illuminate\Database\Seeder;

class SolutionImageSeeder extends Seeder
{
    public function run(): void
    {
        $solutions = Solution::all();

        foreach ($solutions as $solution) {

            $images = [
                "solutions/demo_{$solution->id}_1.jpg",
                "solutions/demo_{$solution->id}_2.jpg",
                "solutions/demo_{$solution->id}_3.jpg",
            ];

            foreach ($images as $index => $img) {
                SolutionImage::create([
                    'solution_id' => $solution->id,
                    'image' => $img,
                    'is_primary' => $index === 0,
                ]);
            }
        }
    }
}

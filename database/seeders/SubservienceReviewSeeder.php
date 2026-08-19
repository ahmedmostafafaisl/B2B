<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubservienceReviewSeeder extends Seeder
{
    public function run(): void
    {
        $sub_services = DB::table('sub_services')->select('id')->get();

        if ($sub_services->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        // 3 reviews لكل sub_service
        foreach ($sub_services as $s) {
            for ($i = 1; $i <= 3; $i++) {
                $rate = (($i - 1) % 5) + 1; // 1..5

                $rows[] = [
                    'sub_service_id' => $s->id,
                    'rate' => $rate,
                    'reviewer_name' => "Reviewer {$i}",
                    'subject' => "Review subject {$i}",
                    'comment' => "This is a sample comment for review {$i}.",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('subservience_reviews')->insert($rows);
    }
}

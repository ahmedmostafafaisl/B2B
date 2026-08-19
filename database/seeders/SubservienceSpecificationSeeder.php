<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubservienceSpecificationSeeder extends Seeder
{
    public function run(): void
    {
        $subserviences = DB::table('sub_services')->select('id')->get();

        if ($subserviences->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        // 5 specs لكل subservience
        foreach ($subserviences as $s) {
            for ($i = 1; $i <= 5; $i++) {
                $rows[] = [
                    'sub_service_id' => $s->id,
                    'type' => ($i % 2 === 0) ? 'general' : 'details',
                    'title' => "Specification {$i}",
                    'description' => "Description for specification {$i}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('subservience_specifications')->insert($rows);
    }
}

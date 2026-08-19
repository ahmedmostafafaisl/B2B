<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSpecificationSeeder extends Seeder
{
    public function run(): void
    {

        $now = Carbon::now();

        // ── Fetch all service type IDs ────────────────────────────────────────
        $serviceTypeIds = DB::table('service_types')->pluck('id');

        if ($serviceTypeIds->isEmpty()) {
            $this->command->warn('No service types found. Please seed service_types first.');

            return;
        }

        // ── Specification templates per type ─────────────────────────────────
        $templates = [
            [
                'type' => 'feature',
                'title' => 'Core Features',
                'description' => 'Main features included in this service type.',
            ],
            [
                'type' => 'feature',
                'title' => 'Advanced Features',
                'description' => 'Advanced capabilities available for premium tiers.',
            ],
            [
                'type' => 'requirement',
                'title' => 'Technical Requirements',
                'description' => 'Minimum technical requirements needed to use this service.',
            ],
            [
                'type' => 'requirement',
                'title' => 'Documentation Requirements',
                'description' => 'Required documents and information before service activation.',
            ],
            [
                'type' => 'limitation',
                'title' => 'Usage Limitations',
                'description' => 'Limits on usage, quota, or frequency for this service type.',
            ],
            [
                'type' => 'limitation',
                'title' => 'Geographic Limitations',
                'description' => 'Regions or countries where this service type is restricted.',
            ],
            [
                'type' => 'benefit',
                'title' => 'Key Benefits',
                'description' => 'Primary benefits provided by this service type.',
            ],
            [
                'type' => 'benefit',
                'title' => 'Support Benefits',
                'description' => 'Support options and SLA included with this service.',
            ],
            [
                'type' => 'term',
                'title' => 'Terms & Conditions',
                'description' => 'General terms and conditions governing the use of this service.',
            ],
            [
                'type' => 'term',
                'title' => 'Cancellation Policy',
                'description' => 'Rules and penalties applicable when cancelling this service.',
            ],
        ];

        // ── Build rows: one set of templates per service type ─────────────────
        $rows = [];

        foreach ($serviceTypeIds as $serviceTypeId) {
            foreach ($templates as $template) {
                $rows[] = [
                    'service_type_id' => $serviceTypeId,
                    'type' => $template['type'],
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // ── Insert in chunks ──────────────────────────────────────────────────
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('service_type_specifications')->insertOrIgnore($chunk);
        }

        $this->command->info(count($rows).' service type specifications seeded successfully.');
    }
}

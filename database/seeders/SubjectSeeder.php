<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'General Inquiry',     'code' => 'GENERAL',     'description' => 'General questions and inquiries'],
            ['name' => 'Technical Support',   'code' => 'TECH',        'description' => 'Technical issues and support requests'],
            ['name' => 'Sales',               'code' => 'SALES',       'description' => 'Sales and pricing inquiries'],
            ['name' => 'Billing',             'code' => 'BILLING',     'description' => 'Billing and payment issues'],
            ['name' => 'Partnership',         'code' => 'PARTNER',     'description' => 'Partnership and collaboration requests'],
            ['name' => 'Complaint',           'code' => 'COMPLAINT',   'description' => 'Complaints and feedback'],
            ['name' => 'Product Inquiry',     'code' => 'PRODUCT',     'description' => 'Questions about products'],
            ['name' => 'Warranty & Service',  'code' => 'WARRANTY',    'description' => 'Warranty claims and service requests'],
            ['name' => 'Shipping & Delivery', 'code' => 'SHIPPING',    'description' => 'Shipping status and delivery issues'],
            ['name' => 'Other',               'code' => 'OTHER',       'description' => 'Other topics not listed above'],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['code' => $subject['code']],
                array_merge($subject, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

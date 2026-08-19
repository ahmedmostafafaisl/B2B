<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $subjectIds = DB::table('subjects')->pluck('id')->toArray();
        $keyIds = DB::table('keys')->pluck('id')->toArray();

        if (empty($subjectIds) || empty($keyIds)) {
            $this->command->warn('Run SubjectSeeder and KeySeeder first.');

            return;
        }

        $statuses = ['new', 'in_progress', 'contacted', 'closed'];

        $contacts = [
            [
                'name' => 'Ahmed Al-Rashidi',
                'email' => 'ahmed.rashidi@example.com',
                'phone' => '+966501234567',
                'message' => 'I would like to inquire about your product pricing.',
                'status' => 'new',
            ],
            [
                'name' => 'Sara Al-Qahtani',
                'email' => 'sara.qahtani@example.com',
                'phone' => '+966512345678',
                'message' => 'My device stopped working after the last update.',
                'status' => 'in_progress',
            ],
            [
                'name' => 'Mohammed Al-Harbi',
                'email' => 'mohammed.harbi@example.com',
                'phone' => '+966523456789',
                'message' => 'We are interested in a bulk order for our company.',
                'status' => 'contacted',
            ],
            [
                'name' => 'Fatima Al-Zahrani',
                'email' => 'fatima.zahrani@example.com',
                'phone' => '+966534567890',
                'message' => 'I have not received my order yet, it has been 2 weeks.',
                'status' => 'closed',
            ],
            [
                'name' => 'Khalid Al-Otaibi',
                'email' => 'khalid.otaibi@example.com',
                'phone' => '+966545678901',
                'message' => 'I want to report an issue with my last invoice.',
                'status' => 'new',
            ],
            [
                'name' => 'Nora Al-Dosari',
                'email' => 'nora.dosari@example.com',
                'phone' => '+966556789012',
                'message' => 'Interested in becoming a reseller partner.',
                'status' => 'in_progress',
            ],
            [
                'name' => 'Omar Al-Shehri',
                'email' => 'omar.shehri@example.com',
                'phone' => '+966567890123',
                'message' => 'Can you explain the warranty policy for filters?',
                'status' => 'new',
            ],
            [
                'name' => 'Lina Al-Ghamdi',
                'email' => 'lina.ghamdi@example.com',
                'phone' => '+966578901234',
                'message' => 'The technician was very professional. Great service!',
                'status' => 'closed',
            ],
        ];

        foreach ($contacts as $index => $contact) {
            DB::table('contacts')->updateOrInsert(
                ['email' => $contact['email']],
                array_merge($contact, [
                    'subject_id' => $subjectIds[$index % count($subjectIds)],
                    'key_id' => $keyIds[$index % count($keyIds)],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\SubPart;
use App\Models\SubpartReview;
use Illuminate\Database\Seeder;

class SubpartReviewSeeder extends Seeder
{
    public function run(): void
    {
        $subParts = SubPart::query()->pluck('id')->all();
        if (empty($subParts)) return;

        $samples = [
            ['rate' => 5, 'reviewer_name' => 'Ahmed Ali', 'subject' => 'Excellent', 'comment' => 'Very good quality'],
            ['rate' => 4, 'reviewer_name' => 'Mohamed Hassan', 'subject' => 'Good', 'comment' => 'Works as expected'],
            ['rate' => 3, 'reviewer_name' => 'Sara Mahmoud', 'subject' => 'Average', 'comment' => 'Needs improvement'],
        ];

        foreach ($subParts as $subPartId) {
            foreach ($samples as $row) {
                SubpartReview::query()->updateOrCreate(
                    [
                        'sub_part_id' => $subPartId,
                        'reviewer_name' => $row['reviewer_name'],
                        'subject' => $row['subject'],
                    ],
                    [
                        'rate' => $row['rate'],
                        'comment' => $row['comment'],
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\SubPart;
use App\Models\SubpartDoc;
use Illuminate\Database\Seeder;

class SubpartDocSeeder extends Seeder
{
    public function run(): void
    {
        $subParts = SubPart::query()->pluck('id')->all();
        if (empty($subParts)) return;

        foreach ($subParts as $subPartId) {
            SubpartDoc::query()->updateOrCreate(
                [
                    'sub_part_id' => $subPartId,
                    'title' => 'Installation Manual',
                ],
                [
                    // placeholder key - replace after upload
                    'file_path' => 'subparts/docs/sample-manual.pdf',
                    'file_original_name' => 'sample-manual.pdf',
                    'file_size' => 123456,
                ]
            );

            SubpartDoc::query()->updateOrCreate(
                [
                    'sub_part_id' => $subPartId,
                    'title' => 'Datasheet',
                ],
                [
                    'file_path' => 'subparts/docs/sample-datasheet.pdf',
                    'file_original_name' => 'sample-datasheet.pdf',
                    'file_size' => 234567,
                ]
            );
        }
    }
}

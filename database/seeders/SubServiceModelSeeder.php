<?php

namespace Database\Seeders;

use App\Models\SubServiceModel;
use App\Models\SubServiceModelItem;
use App\Models\SubServiceModelSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServiceModelSeeder extends Seeder
{
    public function run(): void
    {
        $subServices = DB::table('sub_services')->select('id')->get();
        if ($subServices->isEmpty()) return;

        foreach ($subServices as $ss) {
            $model = SubServiceModel::query()->updateOrCreate(
                [
                    'sub_service_id' => $ss->id,
                    'title' => 'CAPACITY RANGE - 10,000 UP TO 1,000,000 GPD',
                ],
                [
                    'image' => null, // set later if you want upload
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            // clear old sections/items
            $sectionIds = $model->sections()->pluck('id')->all();
            if (!empty($sectionIds)) {
                SubServiceModelItem::query()->whereIn('sub_service_model_section_id', $sectionIds)->delete();
            }
            $model->sections()->delete();

            $sections = [
                [
                    'title' => 'Operation Specifics',
                    'sort_order' => 1,
                    'items' => [
                        'Power supply: 460V/3Ph/60Hz',
                        'Temperature 25°C (max. 40°C)',
                        'TOC <10 max. < 40 mg/L',
                    ],
                ],
                [
                    'title' => 'Operation Specifics (Cont.)',
                    'sort_order' => 2,
                    'items' => [
                        'pH 6-9 (2-11 cleaning)',
                        'TSS < 50 max. < 100 mg/L',
                        'Backwash frequency 20-60 minutes',
                    ],
                ],
                [
                    'title' => 'Operation Specifics (More)',
                    'sort_order' => 3,
                    'items' => [
                        'Turbidity < 50 max. 300 NTU',
                        'Cl2 0.5 ppm (2,000 cleaning)',
                        'COD < 20 max. < 60 mg/L',
                        'Max oil & grease: 2mg/L',
                    ],
                ],
            ];

            foreach ($sections as $sIndex => $s) {
                $section = SubServiceModelSection::query()->create([
                    'sub_service_model_id' => $model->id,
                    'title' => $s['title'],
                    'sort_order' => $s['sort_order'] ?? ($sIndex + 1),
                ]);

                foreach ($s['items'] as $iIndex => $text) {
                    SubServiceModelItem::query()->create([
                        'sub_service_model_section_id' => $section->id,
                        'text' => $text,
                        'sort_order' => $iIndex + 1,
                    ]);
                }
            }
        }
    }
}

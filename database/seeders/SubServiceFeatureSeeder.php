<?php

namespace Database\Seeders;

use App\Models\SubServiceFeature;
use App\Models\SubServiceFeatureItem;
use App\Models\SubServiceFeatureType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubServiceFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $subServices = DB::table('sub_services')->select('id')->get();

        if ($subServices->isEmpty()) {
            return;
        }

        $disk = 's3';
        $imageUrl = 'https://cdn11.bigcommerce.com/s-1x0ys7yqwf/images/stencil/original/image-manager/uf-tab-feature.jpg?t=1695749625';

        // Items exactly like screenshot
        $types = [
            [
                'name' => 'Standard Features',
                'sort_order' => 1,
                'items' => [
                    'High-strength, hollow-fiber membranes',
                    'Stainless steel backwash pump',
                    'Automatic screen filter',
                    'Schedule 80 PVC piping',
                    'PLC control panel',
                    'Automatic backwash',
                    'NEMA 12 enclosure',
                    'Digital flowmeter',
                    'Power coated carbon steel skid',
                    'Liquid filled pressure gauges (panel mount)',
                    'Electrically actuated valves',
                ],
            ],
            [
                'name' => 'Available Options',
                'sort_order' => 2,
                'items' => [
                    'Chemically Enhanced Backwash (CEB)',
                    'Feed/backwash oxidizer (dosing system)',
                    'Membrane cleaning skid (CIP)',
                    '380-415V/3Ph/50Hz power supply',
                    'Turbidity monitor',
                    'Stainless steel multi-stage feed pump',
                    'Feed pump VFD',
                    'PLC + HMI',
                    'Filtrate (backwash) tank air compressor',
                    'Pressure Transducers',
                    'Air scour',
                    'Duplex 2205 or SS 316 automatic screen filter',
                ],
            ],
        ];

        // Download the image ONCE
        $imageBytes = $this->downloadImage($imageUrl);
        $now = now();

        foreach ($subServices as $ss) {
            // Create or get feature (avoid duplicates)
            $feature = SubServiceFeature::query()->firstOrCreate(
                [
                    'sub_service_id' => $ss->id,
                    'title' => 'FEATURES',
                ],
                [
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // If feature has no image, upload to S3
            if (!$feature->image && $imageBytes) {
                $path = "subservices/features/{$ss->id}/" . Str::uuid()->toString() . ".jpg";
                Storage::disk($disk)->put($path, $imageBytes, 's3');

                $feature->update([
                    'image' => $path,
                ]);
            }

            // Rebuild types & items (simple and clean)
            DB::transaction(function () use ($feature, $types) {
                // Delete old nested rows
                $existingTypeIds = $feature->types()->pluck('id')->all();
                if (!empty($existingTypeIds)) {
                    SubServiceFeatureItem::query()
                        ->whereIn('sub_service_feature_type_id', $existingTypeIds)
                        ->delete();
                }
                $feature->types()->delete();

                // Insert new
                foreach ($types as $tIndex => $t) {
                    $type = SubServiceFeatureType::query()->create([
                        'sub_service_feature_id' => $feature->id,
                        'name' => $t['name'],
                        'sort_order' => $t['sort_order'] ?? ($tIndex + 1),
                    ]);

                    foreach ($t['items'] as $iIndex => $text) {
                        SubServiceFeatureItem::query()->create([
                            'sub_service_feature_type_id' => $type->id,
                            'text' => $text,
                            'sort_order' => $iIndex + 1,
                        ]);
                    }
                }
            });
        }
    }

    private function downloadImage(string $url): ?string
    {
        try {
            // Needs Guzzle (Laravel includes it)
            $res = Http::timeout(20)->get($url);

            if (!$res->successful()) {
                return null;
            }

            return $res->body();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

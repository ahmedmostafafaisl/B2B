<?php

namespace Database\Seeders;

use App\Models\Solution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SolutionSeeder extends Seeder
{
    public function run(): void
    {
        $solutions = [
            [
                'title' => 'Contractors, Consultants & NGOs',
                'description' => 'Professional water treatment solutions for contractors, consultants, and NGOs.',
                'details' => [
                    'Focus:',
                    '• Authority-aligned designs',
                    '• Review-ready submittals',
                    '• Clear calculations & data sheets',
                    '• Material & specification compliance',
                    '• QA/QC, FAT & commissioning documentation',
                    '• Coordinated execution with contractors',
                    '• Competitive & transparent proposals',
                    '• Fast technical response',
                    '• Site coordination support',
                    '• On-time delivery',
                    '• Clear warranty terms',
                ],
                'organizations' => [
                    'Diriyah Gate Development Company',
                    'Arkie Company',
                    'Sharouf Contracting Company',
                    'Experts Authority at the Council of Ministers',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 1,
            ],
            [
                'title' => 'Charitable Projects',
                'description' => 'Clean water solutions for charitable and humanitarian projects.',
                'details' => [
                    'Focus:',
                    '• Community water access',
                    '• Sustainable treatment solutions',
                    '• Easy operation and maintenance',
                    '• Fast deployment',
                    '• Budget-conscious engineering',
                ],
                'organizations' => [
                    'NGO Partner 1',
                    'NGO Partner 2',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 2,
            ],
            [
                'title' => 'Residential & Commercial Buildings',
                'description' => 'Reliable water treatment and pressure systems for apartments, villas, and commercial complexes.',
                'details' => [
                    'Focus:',
                    '• Stable water pressure',
                    '• Safe potable water',
                    '• Compact system layouts',
                    '• Easy maintenance',
                    '• Energy-efficient operation',
                ],
                'organizations' => [
                    'Residential Developer 1',
                    'Commercial Complex 1',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 3,
            ],
            [
                'title' => 'Hospitals & Clinics',
                'description' => 'Medical-grade water purification systems for hospitals and clinics.',
                'details' => [
                    'Focus:',
                    '• High purity water',
                    '• Reliable disinfection',
                    '• Compliance support',
                    '• Continuous operation',
                    '• Low maintenance downtime',
                ],
                'organizations' => [
                    'Hospital Group 1',
                    'Clinic Network 1',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 4,
            ],
            [
                'title' => 'Hotels & Resorts',
                'description' => 'Water treatment solutions for hotels and resorts.',
                'details' => [
                    'Focus:',
                    '• Guest water quality',
                    '• Centralized treatment',
                    '• Pressure boosting',
                    '• Scalable design',
                    '• Reliable long-term operation',
                ],
                'organizations' => [
                    'Hotel Chain 1',
                    'Resort Group 1',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 5,
            ],
            [
                'title' => 'Industrial & Manufacturing',
                'description' => 'Industrial water treatment systems for factories and plants.',
                'details' => [
                    'Focus:',
                    '• Process water quality',
                    '• Equipment protection',
                    '• High-capacity systems',
                    '• Custom engineering',
                    '• Reliable industrial performance',
                ],
                'organizations' => [
                    'Factory Group 1',
                    'Industrial Plant 1',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 6,
            ],
            [
                'title' => 'Food & Beverage',
                'description' => 'Food-grade water purification systems for beverage and food industries.',
                'details' => [
                    'Focus:',
                    '• Food-safe water quality',
                    '• Taste and odor control',
                    '• Reliable disinfection',
                    '• Process consistency',
                    '• Hygiene-focused design',
                ],
                'organizations' => [
                    'Beverage Company 1',
                    'Food Manufacturer 1',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 7,
            ],
            [
                'title' => 'Agriculture & Irrigation',
                'description' => 'Efficient irrigation and water treatment solutions for agriculture.',
                'details' => [
                    'Focus:',
                    '• Sustainable irrigation',
                    '• Water reuse solutions',
                    '• Nutrient management',
                    '• Scalable systems',
                    '• Reliable agricultural performance',
                ],
                'organizations' => [
                    'Farm Group 1',
                    'Agricultural Project 1',
                ],
                'banner' => null,
                'icon' => null,
                'sort_order' => 8,
            ],
        ];

        foreach ($solutions as $solution) {
            Solution::create([
                'title' => $solution['title'],
                'slug' => Str::slug($solution['title']),
                'description' => $solution['description'],
                'details' => $solution['details'],
                'organizations' => $solution['organizations'],
                'banner' => $solution['banner'],
                'icon' => $solution['icon'],
                'is_active' => true,
                'sort_order' => $solution['sort_order'],
            ]);
        }
    }
}

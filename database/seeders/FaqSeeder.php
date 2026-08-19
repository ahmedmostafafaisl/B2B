<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SubPart;
use App\Models\SubService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        $groups = [
            [
                'model' => Service::class,
                'items' => Service::query()->select('id', 'title', 'subtitle')->get(),
                'label' => fn ($item) => $item->title,
                'builder' => function ($item) {
                    $name = $item->title;

                    return [
                        [
                            'question' => "What industries can {$name} be used in?",
                            'answer' => "{$name} can be used in industrial, commercial, and municipal applications depending on feed water quality, capacity requirements, and project scope.",
                        ],
                        [
                            'question' => "Is customization available for {$name}?",
                            'answer' => "Yes, {$name} can be customized based on flow rate, water analysis, site conditions, automation level, and required water quality.",
                        ],
                        [
                            'question' => "Does {$name} require regular maintenance?",
                            'answer' => "Yes, regular maintenance is recommended for {$name} to ensure reliable operation, stable performance, and long equipment life.",
                        ],
                    ];
                },
            ],

            [
                'model' => ServiceType::class,
                'items' => ServiceType::query()->select('id', 'name', 'title', 'code')->get(),
                'label' => fn ($item) => $item->title ?: $item->name,
                'builder' => function ($item) {
                    $name = $item->title ?: $item->name;

                    return [
                        [
                            'question' => "What applications is {$name} suitable for?",
                            'answer' => "{$name} is suitable for a wide range of industrial and commercial treatment applications based on operating requirements and target water quality.",
                        ],
                        [
                            'question' => "Can {$name} be customized?",
                            'answer' => "Yes, {$name} can be configured and customized to match project-specific process, layout, and performance requirements.",
                        ],
                        [
                            'question' => "What are the main benefits of {$name}?",
                            'answer' => "The main benefits of {$name} include reliable performance, flexible design options, and suitability for different operating conditions.",
                        ],
                    ];
                },
            ],

            [
                'model' => SubService::class,
                'items' => SubService::query()->select('id', 'title')->get(),
                'label' => fn ($item) => $item->title,
                'builder' => function ($item) {
                    $name = $item->title;

                    return [
                        [
                            'question' => "What is the function of {$name}?",
                            'answer' => "{$name} is designed to support specific treatment or process requirements as part of the overall system configuration.",
                        ],
                        [
                            'question' => "Is {$name} available in multiple capacities?",
                            'answer' => "Yes, {$name} may be available in different capacities and configurations depending on project requirements and operating conditions.",
                        ],
                        [
                            'question' => "Does {$name} need periodic inspection?",
                            'answer' => "Yes, periodic inspection and maintenance are recommended for {$name} to ensure efficiency, safety, and long-term reliability.",
                        ],
                    ];
                },
            ],

            [
                'model' => Part::class,
                'items' => Part::query()->select('id', 'title', 'subtitle')->get(),
                'label' => fn ($item) => $item->title ?: $item->subtitle,
                'builder' => function ($item) {
                    $name = $item->title ?: $item->subtitle;

                    return [
                        [
                            'question' => "What is {$name} used for?",
                            'answer' => "{$name} is used as a system component to support operation, improve reliability, and help maintain required performance.",
                        ],
                        [
                            'question' => "Is {$name} compatible with multiple systems?",
                            'answer' => "Compatibility of {$name} depends on system design, technical specifications, and operating requirements.",
                        ],
                        [
                            'question' => "How often should {$name} be replaced?",
                            'answer' => "Replacement intervals for {$name} depend on operating conditions, usage rate, and maintenance practices.",
                        ],
                    ];
                },
            ],

            [
                'model' => SubPart::class,
                'items' => SubPart::query()->select('id', 'title', 'slug')->get(),
                'label' => fn ($item) => $item->title ?: $item->slug,
                'builder' => function ($item) {
                    $name = $item->title ?: $item->slug;

                    return [
                        [
                            'question' => "What role does {$name} play?",
                            'answer' => "{$name} supports the parent component and contributes to the overall functionality and stability of the system.",
                        ],
                        [
                            'question' => "Can {$name} be ordered separately?",
                            'answer' => "Availability of {$name} depends on stock, spare parts policy, and the related system configuration.",
                        ],
                        [
                            'question' => "Does {$name} require maintenance?",
                            'answer' => "Yes, depending on usage and operating conditions, {$name} may require routine inspection, servicing, or replacement.",
                        ],
                    ];
                },
            ],
        ];

        foreach ($groups as $group) {
            $modelClass = $group['model'];
            $items = $group['items'];
            $builder = $group['builder'];

            foreach ($items as $item) {
                $faqs = $builder($item);

                foreach ($faqs as $index => $faq) {
                    $rows[] = [
                        'faqable_type' => $modelClass,
                        'faqable_id' => $item->id,
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                        'sort_order' => $index + 1,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            DB::table('faqs')->insert($rows);
        }
    }
}

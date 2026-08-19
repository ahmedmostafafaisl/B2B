<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $blogs = [
            [
                'image' => 'blog-1.png',
                'title' => 'Advanced Water Filtration Solutions',
                'desc' => 'Explore modern filtration options that improve taste, safety, and long-term system performance.',
                'published_at' => '2026-04-05 00:50:00',
                'description_points' => [
                    'Automation and monitoring: better visibility of quality and consumption',
                    'Advanced membranes: improved efficiency and longer service life',
                    'Reuse strategies: reclaim water from processes where possible',
                    'Optimized dosing: improves consistency and reduces chemical waste',
                ],
                'sections' => [
                    [
                        'type' => 'paragraph',
                        'content' => 'Clean water starts with choosing the right filtration approach for your needs. Modern systems typically combine multiple stages (sediment, carbon, membrane/RO, and sometimes UV) to remove particles, odors, chlorine, and dissolved contaminants.',
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => 'For homes, the most common upgrade is a multi-stage filter that improves taste and protects appliances. For businesses, filtration is often designed around flow rate, consistency, and compliance requirements.',
                    ],
                    [
                        'type' => 'bullets',
                        'content' => [
                            'Sediment filters: protect downstream filters by removing sand and rust',
                            'Carbon filters: reduce chlorine, odors, and improve taste',
                            'Reverse osmosis: targets dissolved solids for high-purity water',
                            'UV sterilization: adds an extra safety step against microorganisms',
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => 'If you\'re unsure what fits your setup, start by checking your water source (municipal, well, tanker), the expected daily usage, and the quality issues you want to solve. A correct design saves money and avoids over-engineering.',
                    ],
                ],
            ],
            [
                'image' => 'blog-2.png',
                'title' => 'Essential Maintenance Tips for Long-Lasting Systems',
                'desc' => 'Simple maintenance routines that prevent breakdowns, protect components, and keep water quality stable.',
                'published_at' => '2026-04-10 09:00:00',
                'description_points' => [
                    'Track pressure and flow: sudden changes can indicate clogging or leaks',
                    'Replace filters on schedule: don\'t wait until performance drops',
                    'Inspect connections and housings: small leaks become big problems',
                    'Sanitize when needed: especially for storage tanks and pre-filters',
                ],
                'sections' => [
                    [
                        'type' => 'paragraph',
                        'content' => 'Water systems perform best when maintenance is predictable—not reactive. Filters and membranes have lifecycles, and replacing them at the right time prevents pressure drops, poor water quality, and unexpected failures.',
                    ],
                    [
                        'type' => 'bullets',
                        'content' => [
                            'Track pressure and flow: sudden changes can indicate clogging or leaks',
                            'Replace filters on schedule: don\'t wait until performance drops',
                            'Inspect connections and housings: small leaks become big problems',
                            'Sanitize when needed: especially for storage tanks and pre-filters',
                        ],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => 'A good habit is to keep a simple log (date, part replaced, notes). This makes troubleshooting faster and helps you plan consumables before they run out.',
                    ],
                ],
            ],
        ];

        foreach ($blogs as $index => $blogData) {
            $blog = Blog::updateOrCreate(
                ['slug' => Str::slug($blogData['title'])],
                [
                    'image' => $blogData['image'],
                    'title' => $blogData['title'],
                    'desc' => $blogData['desc'],
                    'description_points' => $blogData['description_points'],
                    'published_at' => $blogData['published_at'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            // Re-seed sections cleanly
            $blog->sections()->delete();

            foreach ($blogData['sections'] as $sectionIndex => $section) {
                BlogSection::create([
                    'blog_id' => $blog->id,
                    'type' => $section['type'],
                    'content' => $section['content'],
                    'sort_order' => $sectionIndex + 1,
                ]);
            }
        }
    }
}

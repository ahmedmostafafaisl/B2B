<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ServiceSeeder::class,
            ServiceTypeSeeder::class,
            SolutionSeeder::class,
            SubServiceSeeder::class,
            SubservienceSpecificationSeeder::class,
            SubservienceReviewSeeder::class,
            SubservienceDocSeeder::class,
            SubServiceFeatureSeeder::class,
            SubServiceApplicationSeeder::class,
            SubServiceModelSeeder::class,
            PartSeeder::class,
            SubPartSeeder::class,
            SubpartSpecificationSeeder::class,
            SubpartReviewSeeder::class,
            SubpartDocSeeder::class,
            SubpartFeatureSeeder::class,
            SubpartModelSeeder::class,
            FaqSeeder::class,
            BannerSeeder::class,
            BlogSeeder::class,
            PartnerSeeder::class,
            SubjectSeeder::class,
            KeySeeder::class,
            ContactSeeder::class,
            ServiceTypeSpecificationSeeder::class,
        ]);
    }
}

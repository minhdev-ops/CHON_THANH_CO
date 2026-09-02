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
            AboutTimelineSeeder::class,
            BannerSeeder::class,
            CategorySeeder::class,
            ApplicationSeeder::class,
            ProductSeeder::class,
            CertificateSeeder::class,
            SettingSeeder::class,
            HomeStatSeeder::class,
            WhyChooseUsSeeder::class,
            NewsSeeder::class,
            FaqSeeder::class,
            ProjectSeeder::class,
            UserSeeder::class,
        ]);
    }
}

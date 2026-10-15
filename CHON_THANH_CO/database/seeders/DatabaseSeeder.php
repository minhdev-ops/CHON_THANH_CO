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
            AboutTimelineSeeder::class,
            BannerSeeder::class,
            CategorySeeder::class,
            ApplicationSeeder::class,
            ProductSeeder::class,
            // Phải sau ProductSeeder: gom các mã cùng dòng về sản phẩm cha.
            ProductVariantSeeder::class,
            // Phải sau ProductVariantSeeder: mọi biến thể dùng chung ứng dụng với cha.
            ProductApplicationSeeder::class,
            CertificateSeeder::class,
            SettingSeeder::class,
            HomeStatSeeder::class,
            WhyChooseUsSeeder::class,
            NewsSeeder::class,
            FaqSeeder::class,
            ProjectSeeder::class,
            // Phải sau ProjectSeeder: gắn product_id cho vật liệu dự án.
            ProjectMaterialLinkSeeder::class,
            PartnerSeeder::class,
            UserSeeder::class,
        ]);
    }
}

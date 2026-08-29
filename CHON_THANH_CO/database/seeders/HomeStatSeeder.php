<?php

namespace Database\Seeders;

use App\Models\HomeStat;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class HomeStatSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $stats = [
            [
                'icon' => 'building',
                'value' => '21+',
                'sort_order' => 1,
                'label_vi' => 'Năm kinh nghiệm',
                'label_en' => 'Years of experience',
            ],
            [
                'icon' => 'factory',
                'value' => '2',
                'sort_order' => 2,
                'label_vi' => 'Nhà máy sản xuất',
                'label_en' => 'Manufacturing plants',
            ],
            [
                'icon' => 'warehouse',
                'value' => '8,000',
                'sort_order' => 3,
                'label_vi' => 'Tấn sản phẩm/năm',
                'label_en' => 'Tons per year',
            ],
            [
                'icon' => 'award',
                'value' => 'ISO 9001',
                'sort_order' => 4,
                'label_vi' => 'Chứng nhận ISO 9001:2015',
                'label_en' => 'ISO 9001:2015 certified',
            ],
            [
                'icon' => 'users',
                'value' => '18',
                'sort_order' => 5,
                'label_vi' => 'Nhân sự chuyên nghiệp',
                'label_en' => 'Professional staff',
            ],
            [
                'icon' => 'truck',
                'value' => '7',
                'sort_order' => 6,
                'label_vi' => 'Xe tải 2.5–18 tấn',
                'label_en' => 'Trucks 2.5–18 tons',
            ],
        ];

        foreach ($stats as $data) {
            $stat = HomeStat::firstOrCreate(
                ['icon' => $data['icon']],
                [
                    'icon' => $data['icon'],
                    'value' => $data['value'],
                    'sort_order' => $data['sort_order'],
                ],
            );

            if ($stat->translations()->count() === 0) {
                $stat->translations()->createMany($this->translations([
                    'vi' => ['label' => $data['label_vi']],
                    'en' => ['label' => $data['label_en']],
                ]));
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\HomeStat;
use Illuminate\Database\Seeder;

class HomeStatSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            [
                'icon' => 'building',
                'value' => '21+',
                'label' => 'Năm kinh nghiệm',
                'sort_order' => 1,
            ],
            [
                'icon' => 'factory',
                'value' => '2',
                'label' => 'Nhà máy sản xuất',
                'sort_order' => 2,
            ],
            [
                'icon' => 'warehouse',
                'value' => '8,000',
                'label' => 'Tấn sản phẩm/năm',
                'sort_order' => 3,
            ],
            [
                'icon' => 'award',
                'value' => 'ISO 9001',
                'label' => 'Chứng nhận ISO 9001:2015',
                'sort_order' => 4,
            ],
            [
                'icon' => 'users',
                'value' => '18',
                'label' => 'Nhân sự chuyên nghiệp',
                'sort_order' => 5,
            ],
            [
                'icon' => 'truck',
                'value' => '7',
                'label' => 'Xe tải 2.5–18 tấn',
                'sort_order' => 6,
            ],
        ];

        foreach ($stats as $data) {
            HomeStat::updateOrCreate(
                ['icon' => $data['icon']],
                [
                    'icon' => $data['icon'],
                    'value' => $data['value'],
                    'label' => $data['label'],
                    'sort_order' => $data['sort_order'],
                ]
            );
        }
    }
}
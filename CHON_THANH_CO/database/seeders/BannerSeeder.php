<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'section' => 'hero',
                'image' => '/images/home-hero.jpg',
                'link_to' => '/contact',
                'translations' => [
                    'vi' => [
                        'title' => 'ĐỐI TÁC UY TÍN VẬT LIỆU ĐỊA KỸ THUẬT',
                        'subtitle' => 'Nhà phân phối uỷ quyền HOCK Technology — Geogrid · Geotextile · Erosion Control',
                        'button_text' => 'TƯ VẤN SẢN PHẨM',
                    ],
                    'en' => [
                        'title' => 'TRUSTED PARTNER FOR GEOSYNTHETIC MATERIALS',
                        'subtitle' => 'Authorized distributor of HOCK Technology — Geogrid · Geotextile · Erosion Control',
                        'button_text' => 'GET A QUOTE',
                    ],
                ],
            ],
            [
                'section' => 'cta',
                'link_to' => '/contact',
                'translations' => [
                    'vi' => [
                        'title' => 'CẦN TƯ VẤN? LIÊN HỆ NGAY',
                        'text' => 'Đội ngũ kỹ sư của chúng tôi sẵn sàng hỗ trợ giải pháp tối ưu cho dự án của bạn.',
                        'button_text' => 'GỬI YÊU CẦU',
                    ],
                    'en' => [
                        'title' => 'NEED ADVICE? CONTACT US NOW',
                        'text' => 'Our engineers are ready to support the optimal solution for your project.',
                        'button_text' => 'SEND REQUEST',
                    ],
                ],
            ],
        ];

        foreach ($banners as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $banner = Banner::updateOrCreate(
                ['section' => $data['section']],
                $data
            );

            $banner->translations()->delete();

            foreach ($translations as $locale => $row) {
                $banner->translations()->create(array_merge(['locale' => $locale], $row));
            }
        }
    }
}

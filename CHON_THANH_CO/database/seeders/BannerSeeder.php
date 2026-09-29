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
                'title' => 'ĐỐI TÁC UY TÍN VẬT LIỆU ĐỊA KỸ THUẬT',
                'subtitle' => 'Nhà phân phối uỷ quyền HOCK Technology — Geogrid · Geotextile · Erosion Control',
                'button_text' => 'TƯ VẤN SẢN PHẨM',
            ],
            [
                'section' => 'cta',
                'link_to' => '/contact',
                'title' => 'CẦN TƯ VẤN? LIÊN HỆ NGAY',
                'text' => 'Đội ngũ kỹ sư của chúng tôi sẵn sàng hỗ trợ giải pháp tối ưu cho dự án của bạn.',
                'button_text' => 'GỬI YÊU CẦU',
            ],
        ];

        foreach ($banners as $data) {
            Banner::updateOrCreate(
                ['section' => $data['section']],
                $data
            );
        }
    }
}
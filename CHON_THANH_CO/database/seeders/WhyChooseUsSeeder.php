<?php

namespace Database\Seeders;

use App\Models\WhyChooseUs;
use Illuminate\Database\Seeder;

class WhyChooseUsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'icon' => 'badge-check',
                'sort_order' => 1,
                'title' => 'Chất lượng đạt chuẩn',
                'description' => 'Sản phẩm sản xuất theo hệ thống quản lý chất lượng ISO 9001:2015, phù hợp TCVN 9844:2013 và tiêu chuẩn châu Âu.',
            ],
            [
                'icon' => 'factory',
                'sort_order' => 2,
                'title' => 'Trực tiếp từ nhà máy',
                'description' => 'Giá cạnh tranh từ hai nhà máy: Rọ đá Á Châu (Hóc Môn) và Lưới Thép Tiên Phong (Đà Nẵng).',
            ],
            [
                'icon' => 'life-buoy',
                'sort_order' => 3,
                'title' => 'Hỗ trợ kỹ thuật miễn phí',
                'description' => 'Tư vấn lựa chọn vật liệu, hướng dẫn lắp đặt và thi công cho mọi dự án, mọi quy mô.',
            ],
            [
                'icon' => 'truck',
                'sort_order' => 4,
                'title' => 'Giao hàng toàn quốc',
                'description' => 'Đội xe tải 2.5–18 tấn giao hàng tận công trình, xuất khẩu sang Campuchia, Lào và Myanmar.',
            ],
            [
                'icon' => 'award',
                'sort_order' => 5,
                'title' => 'Uy tín hơn ' . (date('Y') - 2005) . ' năm',
                'description' => 'Hoạt động từ năm 2005, đồng hành cùng hàng nghìn công trình hạ tầng trên toàn quốc.',
            ],
            [
                'icon' => 'users',
                'sort_order' => 6,
                'title' => 'Đội ngũ chuyên nghiệp',
                'description' => '18 nhân sự giàu kinh nghiệm, tư vấn và xử lý đơn hàng nhanh chóng, chính xác.',
            ],
        ];

        foreach ($items as $data) {
            WhyChooseUs::updateOrCreate(
                ['icon' => $data['icon']],
                [
                    'icon' => $data['icon'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'sort_order' => $data['sort_order'],
                ]
            );
        }
    }
}
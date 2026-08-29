<?php

namespace Database\Seeders;

use App\Models\WhyChooseUs;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class WhyChooseUsSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $items = [
            [
                'icon' => 'badge-check',
                'sort_order' => 1,
                'vi' => [
                    'title' => 'Chất lượng đạt chuẩn',
                    'description' => 'Sản phẩm sản xuất theo hệ thống quản lý chất lượng ISO 9001:2015, phù hợp TCVN 9844:2013 và tiêu chuẩn châu Âu.',
                ],
                'en' => [
                    'title' => 'Certified quality',
                    'description' => 'Products manufactured under ISO 9001:2015 quality management, conforming to TCVN 9844:2013 and European standards.',
                ],
            ],
            [
                'icon' => 'factory',
                'sort_order' => 2,
                'vi' => [
                    'title' => 'Trực tiếp từ nhà máy',
                    'description' => 'Giá cạnh tranh từ hai nhà máy: Rọ đá Á Châu (Hóc Môn) và Lưới Thép Tiên Phong (Đà Nẵng).',
                ],
                'en' => [
                    'title' => 'Direct from factory',
                    'description' => 'Competitive pricing from our two plants: A Chau Gabion (Hoc Mon) and Tien Phong Steel Mesh (Da Nang).',
                ],
            ],
            [
                'icon' => 'life-buoy',
                'sort_order' => 3,
                'vi' => [
                    'title' => 'Hỗ trợ kỹ thuật miễn phí',
                    'description' => 'Tư vấn lựa chọn vật liệu, hướng dẫn lắp đặt và thi công cho mọi dự án, mọi quy mô.',
                ],
                'en' => [
                    'title' => 'Free technical support',
                    'description' => 'Consultation on material selection, installation and construction guidance for projects of any scale.',
                ],
            ],
            [
                'icon' => 'truck',
                'sort_order' => 4,
                'vi' => [
                    'title' => 'Giao hàng toàn quốc',
                    'description' => 'Đội xe tải 2.5–18 tấn giao hàng tận công trình, xuất khẩu sang Campuchia, Lào và Myanmar.',
                ],
                'en' => [
                    'title' => 'Nationwide delivery',
                    'description' => 'Our fleet of 2.5–18 ton trucks delivers to site, with exports to Cambodia, Laos and Myanmar.',
                ],
            ],
            [
                'icon' => 'award',
                'sort_order' => 5,
                'vi' => [
                    'title' => 'Uy tín hơn ' . (date('Y') - 2005) . ' năm',
                    'description' => 'Hoạt động từ năm 2005, đồng hành cùng hàng nghìn công trình hạ tầng trên toàn quốc.',
                ],
                'en' => [
                    'title' => (date('Y') - 2005) . '+ years of trust',
                    'description' => 'Operating since 2005, partnering with thousands of infrastructure projects nationwide.',
                ],
            ],
            [
                'icon' => 'users',
                'sort_order' => 6,
                'vi' => [
                    'title' => 'Đội ngũ chuyên nghiệp',
                    'description' => '18 nhân sự giàu kinh nghiệm, tư vấn và xử lý đơn hàng nhanh chóng, chính xác.',
                ],
                'en' => [
                    'title' => 'Professional team',
                    'description' => '18 experienced staff providing fast and accurate consultation and order handling.',
                ],
            ],
        ];

        foreach ($items as $data) {
            $item = WhyChooseUs::firstOrCreate(
                ['icon' => $data['icon']],
                [
                    'icon' => $data['icon'],
                    'sort_order' => $data['sort_order'],
                ],
            );

            if ($item->translations()->count() === 0) {
                $item->translations()->createMany($this->translations([
                    'vi' => $data['vi'],
                    'en' => $data['en'],
                ]));
            }
        }
    }
}

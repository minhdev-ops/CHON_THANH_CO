<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applications = [
            [
                'slug' => 'phan-cach-loc',
                'sort_order' => 1,
                'name' => 'Phân cách, lọc',
                'description' => 'Ngăn cách các lớp đất, lọc và ngăn vật liệu hạt mịn bị cuốn trôi.',
            ],
            [
                'slug' => 'thoat-nuoc',
                'sort_order' => 2,
                'name' => 'Thoát nước',
                'description' => 'Thu và thoát nước theo phương ngang, giảm áp lực nước lỗ rỗng.',
            ],
            [
                'slug' => 'gia-co-nen',
                'sort_order' => 3,
                'name' => 'Gia cố nền',
                'description' => 'Tăng sức chịu tải và giảm lún cho nền đất yếu.',
            ],
            [
                'slug' => 'chong-xoi-mon',
                'sort_order' => 4,
                'name' => 'Chống xói mòn',
                'description' => 'Bảo vệ bề mặt đất khỏi xói mòn do mưa và dòng chảy.',
            ],
            [
                'slug' => 'on-dinh-mai-doc',
                'sort_order' => 5,
                'name' => 'Ổn định mái dốc',
                'description' => 'Giữ ổn định mái dốc, tường chắn và bờ kè.',
            ],
            [
                'slug' => 'chong-tham',
                'sort_order' => 6,
                'name' => 'Chống thấm',
                'description' => 'Chống thấm tuyệt đối cho hồ chứa, kênh mương và bãi chôn lấp.',
            ],
            [
                'slug' => 'hang-rao-bao-ve',
                'sort_order' => 7,
                'name' => 'Hàng rào & bảo vệ',
                'description' => 'Hàng rào bảo vệ công trình, khu dân cư và an ninh.',
            ],
        ];

        foreach ($applications as $data) {
            Application::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order'], 'name' => $data['name'], 'description' => $data['description']]
            );
        }
    }
}
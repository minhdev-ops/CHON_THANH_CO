<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'vai-kt-khong-det',
                'sort_order' => 1,
                'name' => 'Vải địa kỹ thuật không dệt',
                'description' => 'Vải địa kỹ thuật không dệt từ sợi PP/PET xuyên kim, dùng cho phân cách, lọc và thoát nước.',
            ],
            [
                'slug' => 'vai-kt-det',
                'sort_order' => 2,
                'name' => 'Vải địa kỹ thuật dệt',
                'description' => 'Vải dệt cường độ cao cho gia cố nền, ổn định mái dốc và tường chắn.',
            ],
            [
                'slug' => 'luoi-dia-ky-thuat',
                'sort_order' => 3,
                'name' => 'Lưới địa kỹ thuật',
                'description' => 'Lưới địa kỹ thuật gia cường nền đường, tường chắn và nền đất yếu.',
            ],
            [
                'slug' => 'tham-3d',
                'sort_order' => 4,
                'name' => 'Thảm 3D chống xói mòn',
                'description' => 'Thảm 3D kiểm soát xói mòn mái dốc, bờ sông và kênh mương.',
            ],
            [
                'slug' => 'ro-da',
                'sort_order' => 5,
                'name' => 'Rọ đá',
                'description' => 'Rọ đá gia cố mái dốc, bờ kè và chống xói mòn.',
            ],
            [
                'slug' => 'bang-tham',
                'sort_order' => 6,
                'name' => 'Băng thấm (GCL)',
                'description' => 'Băng thấm bentonite chống thấm cho hồ chứa, bãi rác và kênh mương.',
            ],
            [
                'slug' => 'mang-chong-tham',
                'sort_order' => 7,
                'name' => 'Màng chống thấm HDPE',
                'description' => 'Màng HDPE chống thấm tuyệt đối cho hồ chứa, bãi chôn lấp và công trình môi trường.',
            ],
            [
                'slug' => 'luoi-thep-day-kem',
                'sort_order' => 8,
                'name' => 'Lưới thép & dây kẽm',
                'description' => 'Lưới thép B40, lưới hàn và dây kẽm gai cho hàng rào bảo vệ công trình.',
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order'], 'name' => $data['name'], 'description' => $data['description']]
            );
        }
    }
}
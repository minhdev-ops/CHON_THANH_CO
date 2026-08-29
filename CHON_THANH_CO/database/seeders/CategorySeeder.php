<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $categories = [
            [
                'slug' => 'vai-kt-khong-det',
                'sort_order' => 1,
                'name_vi' => 'Vải địa kỹ thuật không dệt',
                'name_en' => 'Non-woven Geotextile',
                'desc_vi' => 'Vải địa kỹ thuật không dệt từ sợi PP/PET xuyên kim, dùng cho phân cách, lọc và thoát nước.',
                'desc_en' => 'Needle-punched non-woven geotextile made of PP/PET fibers for separation, filtration and drainage.',
            ],
            [
                'slug' => 'vai-kt-det',
                'sort_order' => 2,
                'name_vi' => 'Vải địa kỹ thuật dệt',
                'name_en' => 'Woven Geotextile',
                'desc_vi' => 'Vải dệt cường độ cao cho gia cố nền, ổn định mái dốc và tường chắn.',
                'desc_en' => 'High-strength woven geotextile for soil reinforcement, slope stabilization and retaining walls.',
            ],
            [
                'slug' => 'luoi-dia-ky-thuat',
                'sort_order' => 3,
                'name_vi' => 'Lưới địa kỹ thuật',
                'name_en' => 'Geogrid',
                'desc_vi' => 'Lưới địa kỹ thuật gia cường nền đường, tường chắn và nền đất yếu.',
                'desc_en' => 'Geogrid reinforcement for road bases, retaining walls and soft soil.',
            ],
            [
                'slug' => 'tham-3d',
                'sort_order' => 4,
                'name_vi' => 'Thảm 3D chống xói mòn',
                'name_en' => '3D Erosion Control Mat',
                'desc_vi' => 'Thảm 3D kiểm soát xói mòn mái dốc, bờ sông và kênh mương.',
                'desc_en' => '3D mat for erosion control on slopes, riverbanks and channels.',
            ],
            [
                'slug' => 'ro-da',
                'sort_order' => 5,
                'name_vi' => 'Rọ đá',
                'name_en' => 'Gabion',
                'desc_vi' => 'Rọ đá gia cố mái dốc, bờ kè và chống xói mòn.',
                'desc_en' => 'Gabions for slope protection, riverbank revetment and erosion control.',
            ],
            [
                'slug' => 'bang-tham',
                'sort_order' => 6,
                'name_vi' => 'Băng thấm (GCL)',
                'name_en' => 'Geosynthetic Clay Liner (GCL)',
                'desc_vi' => 'Băng thấm bentonite chống thấm cho hồ chứa, bãi rác và kênh mương.',
                'desc_en' => 'Bentonite geosynthetic clay liner for ponds, landfills and channels.',
            ],
            [
                'slug' => 'mang-chong-tham',
                'sort_order' => 7,
                'name_vi' => 'Màng chống thấm HDPE',
                'name_en' => 'HDPE Geomembrane',
                'desc_vi' => 'Màng HDPE chống thấm tuyệt đối cho hồ chứa, bãi chôn lấp và công trình môi trường.',
                'desc_en' => 'HDPE geomembrane for absolute waterproofing of reservoirs, landfills and environmental works.',
            ],
            [
                'slug' => 'luoi-thep-day-kem',
                'sort_order' => 8,
                'name_vi' => 'Lưới thép & dây kẽm',
                'name_en' => 'Wire Mesh & Barbed Wire',
                'desc_vi' => 'Lưới thép B40, lưới hàn và dây kẽm gai cho hàng rào bảo vệ công trình.',
                'desc_en' => 'B40 wire mesh, welded mesh and barbed wire for site fencing.',
            ],
        ];

        foreach ($categories as $data) {
            $category = Category::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order']]
            );

            $category->translations()->delete();
            $category->translations()->createMany($this->translations([
                'vi' => [
                    'name' => $data['name_vi'],
                    'description' => $data['desc_vi'],
                ],
                'en' => [
                    'name' => $data['name_en'],
                    'description' => $data['desc_en'],
                ],
            ]));
        }
    }
}

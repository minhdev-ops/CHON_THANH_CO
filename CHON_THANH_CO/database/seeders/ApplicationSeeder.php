<?php

namespace Database\Seeders;

use App\Models\Application;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $applications = [
            [
                'slug' => 'phan-cach-loc',
                'sort_order' => 1,
                'name_vi' => 'Phân cách, lọc',
                'name_en' => 'Separation & Filtration',
                'desc_vi' => 'Ngăn cách các lớp đất, lọc và ngăn vật liệu hạt mịn bị cuốn trôi.',
                'desc_en' => 'Separates soil layers and filters fine particles from being washed out.',
            ],
            [
                'slug' => 'thoat-nuoc',
                'sort_order' => 2,
                'name_vi' => 'Thoát nước',
                'name_en' => 'Drainage',
                'desc_vi' => 'Thu và thoát nước theo phương ngang, giảm áp lực nước lỗ rỗng.',
                'desc_en' => 'Collects and drains water laterally, relieving pore water pressure.',
            ],
            [
                'slug' => 'gia-co-nen',
                'sort_order' => 3,
                'name_vi' => 'Gia cố nền',
                'name_en' => 'Soil Reinforcement',
                'desc_vi' => 'Tăng sức chịu tải và giảm lún cho nền đất yếu.',
                'desc_en' => 'Increases bearing capacity and reduces settlement of soft soil.',
            ],
            [
                'slug' => 'chong-xoi-mon',
                'sort_order' => 4,
                'name_vi' => 'Chống xói mòn',
                'name_en' => 'Erosion Control',
                'desc_vi' => 'Bảo vệ bề mặt đất khỏi xói mòn do mưa và dòng chảy.',
                'desc_en' => 'Protects soil surface from erosion caused by rain and runoff.',
            ],
            [
                'slug' => 'on-dinh-mai-doc',
                'sort_order' => 5,
                'name_vi' => 'Ổn định mái dốc',
                'name_en' => 'Slope Stabilization',
                'desc_vi' => 'Giữ ổn định mái dốc, tường chắn và bờ kè.',
                'desc_en' => 'Stabilizes slopes, retaining walls and revetments.',
            ],
            [
                'slug' => 'chong-tham',
                'sort_order' => 6,
                'name_vi' => 'Chống thấm',
                'name_en' => 'Waterproofing / Containment',
                'desc_vi' => 'Chống thấm tuyệt đối cho hồ chứa, kênh mương và bãi chôn lấp.',
                'desc_en' => 'Absolute waterproofing for reservoirs, channels and landfills.',
            ],
            [
                'slug' => 'hang-rao-bao-ve',
                'sort_order' => 7,
                'name_vi' => 'Hàng rào & bảo vệ',
                'name_en' => 'Fencing & Protection',
                'desc_vi' => 'Hàng rào bảo vệ công trình, khu dân cư và an ninh.',
                'desc_en' => 'Site fencing and security protection.',
            ],
        ];

        foreach ($applications as $data) {
            $application = Application::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order']]
            );

            $application->translations()->delete();
            $application->translations()->createMany($this->translations([
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

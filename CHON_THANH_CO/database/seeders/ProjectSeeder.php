<?php

namespace Database\Seeders;

use App\Models\Project;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $projects = [
            [
                'slug' => 'cao-toc-bac-bac-quang-nam',
                'period' => '2024 - 2025',
                'area' => '50,000 m²',
                'hero_image' => '/images/projects/highway-1.jpg',
                'desc_image' => '/images/products/geotextile-roll.jpg',
                'sort_order' => 1,
                'vi' => [
                    'name' => 'Đường cao tốc Bắc Bắc — Quảng Nam',
                    'location' => 'Quảng Nam',
                    'description' => "Dự án đường cao tốc Bắc Bắc đoạn qua Quảng Nam là một trong những công trình hạ tầng trọng điểm quốc gia, đòi hỏi tiêu chuẩn kỹ thuật khắt khe về nền móng và độ ổn định lâu dài.\n\nTrong bối cảnh địa chất phức tạp tại khu vực miền Trung, giải pháp gia cố nền đất yếu bằng vật liệu địa kỹ thuật tiên tiến được ưu tiên hàng đầu nhằm đảm bảo an toàn kết cấu và tối ưu chi phí vòng đời dự án.",
                ],
                'en' => [
                    'name' => 'Bac Bac Expressway — Quang Nam',
                    'location' => 'Quang Nam',
                    'description' => "The Bac Bac expressway section through Quang Nam is a key national infrastructure project demanding strict technical standards for foundations and long-term stability.\n\nGiven the complex geology of Central Vietnam, advanced geosynthetic reinforcement of soft soil was prioritized to ensure structural safety and optimize life-cycle costs.",
                ],
                'materials' => [
                    ['name_vi' => 'Lưới địa kỹ thuật', 'name_en' => 'Geogrid', 'detail_vi' => 'Gia cố cốt nền đường, tăng khả năng chịu tải trọng và giảm sụt lún không đều.', 'detail_en' => 'Road base reinforcement, increasing load capacity and reducing differential settlement.', 'img' => '/images/products/industrial-1.jpg'],
                    ['name_vi' => 'Vải địa kỹ thuật không dệt', 'name_en' => 'Non-woven geotextile', 'detail_vi' => 'Chức năng phân cách và lọc thoát nước bảo vệ nền đường cao tốc.', 'detail_en' => 'Separation and filtration protecting the expressway subgrade.', 'img' => '/images/products/geotextile-roll.jpg'],
                ],
                'gallery' => [
                    '/images/projects/highway-2.jpg',
                    '/images/projects/highway-3.jpg',
                    '/images/projects/highway-4.jpg',
                    '/images/projects/highway-5.jpg',
                    '/images/projects/highway-6.jpg',
                    '/images/projects/highway-7.jpg',
                ],
            ],
            [
                'slug' => 'ho-thuy-tien-thua-thien-hue',
                'period' => '2023 - 2024',
                'area' => '30,000 m²',
                'hero_image' => '/images/projects/lake-1.jpg',
                'desc_image' => '/images/products/geotextile-roll.jpg',
                'sort_order' => 2,
                'vi' => [
                    'name' => 'Hồ Thuỷ Tiên — Thừa Thiên Huế',
                    'location' => 'Thừa Thiên Huế',
                    'description' => "Dự án hồ chứa nước Thuỷ Tiên là công trình thuỷ lợi quan trọng phục vụ tưới tiêu cho hàng nghìn hecta đất nông nghiệp tại tỉnh Thừa Thiên Huế.\n\nCHON THANH đã cung cấp giải pháp thảm 3D chống xói mòn và vải địa kỹ thuật cho toàn bộ hệ thống kênh mương và đập chính.",
                ],
                'en' => [
                    'name' => 'Thuy Tien Reservoir — Thua Thien Hue',
                    'location' => 'Thua Thien Hue',
                    'description' => "The Thuy Tien reservoir is a vital irrigation work serving thousands of hectares of farmland in Thua Thien Hue province.\n\nCHON THANH supplied 3D erosion control mats and geotextiles for the entire canal system and the main dam.",
                ],
                'materials' => [
                    ['name_vi' => 'Thảm 3D chống xói mòn', 'name_en' => '3D erosion control mat', 'detail_vi' => 'Bảo vệ bờ kè và mái dốc hồ chứa khỏi xói mòn do dòng chảy.', 'detail_en' => 'Protects the embankment and slopes of the reservoir from flow erosion.', 'img' => '/images/products/gabion-1.jpg'],
                ],
                'gallery' => [
                    '/images/projects/lake-1.jpg',
                    '/images/projects/highway-1.jpg',
                    '/images/projects/highway-2.jpg',
                ],
            ],
            [
                'slug' => 'khu-cong-nghiep-ha-noi',
                'period' => '2024',
                'area' => '100,000 m²',
                'hero_image' => '/images/projects/highway-1.jpg',
                'desc_image' => '/images/projects/highway-2.jpg',
                'sort_order' => 3,
                'vi' => [
                    'name' => 'Khu công nghiệp Hà Nội',
                    'location' => 'Hà Nội',
                    'description' => "Dự án hạ tầng khu công nghiệp mới tại Hà Nội với quy mô lớn, yêu cầu xử lý nền đất yếu trên diện rộng.\n\nCHON THANH đã cung cấp lưới địa kỹ thuật gia cố nền đường nội bộ và vải địa kỹ thuật phân cách cho toàn bộ hạ tầng giao thông nội khu.",
                ],
                'en' => [
                    'name' => 'Hanoi Industrial Park',
                    'location' => 'Hanoi',
                    'description' => "A large-scale new industrial park project in Hanoi requiring soft soil treatment over a wide area.\n\nCHON THANH supplied geogrids for internal road base reinforcement and geotextiles for separation throughout the park's transport infrastructure.",
                ],
                'materials' => [
                    ['name_vi' => 'Lưới địa kỹ thuật', 'name_en' => 'Geogrid', 'detail_vi' => 'Gia cố nền đường nội bộ bằng lưới địa kỹ thuật cốt sợi thủy tinh.', 'detail_en' => 'Internal road base reinforcement with fiberglass geogrid.', 'img' => '/images/products/industrial-1.jpg'],
                    ['name_vi' => 'Vải địa kỹ thuật không dệt', 'name_en' => 'Non-woven geotextile', 'detail_vi' => 'Phân cách và lọc thoát nền đường khu công nghiệp.', 'detail_en' => 'Separation and filtration for industrial park road subgrade.', 'img' => '/images/products/geotextile-roll.jpg'],
                ],
                'gallery' => [
                    '/images/projects/highway-1.jpg',
                    '/images/projects/highway-2.jpg',
                ],
            ],
            [
                'slug' => 'cau-song-han-da-nang',
                'period' => '2023 - 2024',
                'area' => '20,000 m²',
                'hero_image' => '/images/projects/bridge-1.jpg',
                'desc_image' => '/images/products/geotextile-roll.jpg',
                'sort_order' => 4,
                'vi' => [
                    'name' => 'Cầu Sông Hàn — Đà Nẵng',
                    'location' => 'Đà Nẵng',
                    'description' => "Dự án cầu Sông Hàn là công trình giao thông trọng điểm tại thành phố Đà Nẵng, sử dụng lưới địa kỹ thuật cường độ cao để gia cố mố cầu và đường dẫn hai đầu cầu.\n\nCHON THANH đã cung cấp giải pháp gia cố nền móng cho các trụ cầu chính, đảm bảo độ ổn định và chịu tải cho công trình vĩnh cửu này.",
                ],
                'en' => [
                    'name' => 'Han River Bridge — Da Nang',
                    'location' => 'Da Nang',
                    'description' => "The Han River bridge is a key transport work in Da Nang city, using high-strength geogrids to reinforce the abutments and approach roads.\n\nCHON THANH supplied foundation reinforcement solutions for the main bridge piers, ensuring stability and load capacity for this permanent structure.",
                ],
                'materials' => [
                    ['name_vi' => 'Lưới địa kỹ thuật', 'name_en' => 'Geogrid', 'detail_vi' => 'Gia cố mố cầu và nền đường dẫn, tăng khả năng chịu tải.', 'detail_en' => 'Reinforcement of bridge abutments and approach roads.', 'img' => '/images/products/industrial-1.jpg'],
                ],
                'gallery' => [
                    '/images/projects/highway-2.jpg',
                    '/images/projects/bridge-1.jpg',
                ],
            ],
            [
                'slug' => 'san-bay-quoc-te-long-thanh',
                'period' => '2024 - 2026',
                'area' => '200,000 m²',
                'hero_image' => '/images/projects/airport-1.jpg',
                'desc_image' => '/images/products/industrial-1.jpg',
                'sort_order' => 5,
                'vi' => [
                    'name' => 'Sân bay quốc tế Long Thành — Đồng Nai',
                    'location' => 'Đồng Nai',
                    'description' => "Dự án sân bay quốc tế Long Thành là siêu công trình hạ tầng trọng điểm quốc gia, yêu cầu giải pháp gia cố nền và chống thấm đồng bộ cho toàn bộ khu vực đường băng, sân đỗ và nhà ga.\n\nCHON THANH đã cung cấp lưới địa kỹ thuật cường độ cao gia cố nền đường băng, vải địa kỹ thuật phân cách và màng HDPE chống thấm cho hệ thống thoát nước và xử lý môi trường.",
                ],
                'en' => [
                    'name' => 'Long Thanh International Airport — Dong Nai',
                    'location' => 'Dong Nai',
                    'description' => "Long Thanh International Airport is a mega national infrastructure project requiring integrated ground reinforcement and waterproofing for the runways, aprons and terminals.\n\nCHON THANH supplied high-strength geogrids for runway subgrade, separation geotextiles and HDPE geomembranes for the drainage and environmental treatment systems.",
                ],
                'materials' => [
                    ['name_vi' => 'Lưới địa kỹ thuật', 'name_en' => 'Geogrid', 'detail_vi' => 'Gia cố nền đường băng sân bay bằng lưới địa kỹ thuật cường độ cao.', 'detail_en' => 'Runway subgrade reinforcement with high-strength geogrid.', 'img' => '/images/products/industrial-1.jpg'],
                    ['name_vi' => 'Màng chống thấm HDPE', 'name_en' => 'HDPE geomembrane', 'detail_vi' => 'Chống thấm hệ thống thu gom nước thải và bảo vệ môi trường khu vực sân bay.', 'detail_en' => 'Waterproofing of the wastewater collection system and environmental protection.', 'img' => '/images/products/industrial-1.jpg'],
                ],
                'gallery' => [
                    '/images/projects/airport-1.jpg',
                    '/images/products/industrial-1.jpg',
                    '/images/projects/highway-2.jpg',
                ],
            ],
            [
                'slug' => 'khu-do-thi-xanh-thu-duc',
                'period' => '2023 - 2025',
                'area' => '80,000 m²',
                'hero_image' => '/images/projects/city-1.jpg',
                'desc_image' => '/images/products/gabion-1.jpg',
                'sort_order' => 6,
                'vi' => [
                    'name' => 'Khu đô thị xanh — Thủ Đức',
                    'location' => 'Thủ Đức, TP.HCM',
                    'description' => "Dự án khu đô thị xanh tại Thủ Đức với định hướng phát triển bền vững và hạ tầng kỹ thuật đồng bộ. CHON THANH cung cấp giải pháp địa kỹ thuật toàn diện cho hệ thống đường nội khu, hồ cảnh quan và tường chắn đất.\n\nGiải pháp kết hợp vải địa kỹ thuật phân cách, thảm 3D chống xói mòn mái dốc đảm bảo công trình vận hành ổn định và thân thiện môi trường.",
                ],
                'en' => [
                    'name' => 'Thu Duc Green Urban Area',
                    'location' => 'Thu Duc, Ho Chi Minh City',
                    'description' => "A green urban development project in Thu Duc oriented toward sustainable growth with integrated technical infrastructure. CHON THANH provided comprehensive geosynthetic solutions for internal roads, landscape lakes and earth retaining walls.\n\nThe combined solution of separation geotextiles and 3D erosion control mats on slopes ensures stable, environmentally friendly operation.",
                ],
                'materials' => [
                    ['name_vi' => 'Vải địa kỹ thuật không dệt', 'name_en' => 'Non-woven geotextile', 'detail_vi' => 'Phân cách nền đường nội bộ, chống lún cho hạ tầng khu đô thị.', 'detail_en' => 'Internal road subgrade separation preventing settlement.', 'img' => '/images/products/geotextile-roll.jpg'],
                    ['name_vi' => 'Thảm 3D chống xói mòn', 'name_en' => '3D erosion control mat', 'detail_vi' => 'Bảo vệ mái dốc và hồ cảnh quan khỏi xói mòn do mưa và dòng chảy.', 'detail_en' => 'Protects slopes and landscape lakes from rain and flow erosion.', 'img' => '/images/products/gabion-1.jpg'],
                ],
                'gallery' => [
                    '/images/projects/city-1.jpg',
                    '/images/products/gabion-1.jpg',
                    '/images/products/industrial-1.jpg',
                ],
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'period' => $data['period'],
                    'area' => $data['area'],
                    'hero_image' => $data['hero_image'],
                    'desc_image' => $data['desc_image'],
                    'sort_order' => $data['sort_order'],
                ]
            );

            $project->translations()->delete();
            $project->translations()->createMany($this->translations([
                'vi' => [
                    'name' => $data['vi']['name'],
                    'location' => $data['vi']['location'],
                    'description' => $data['vi']['description'],
                ],
                'en' => [
                    'name' => $data['en']['name'],
                    'location' => $data['en']['location'],
                    'description' => $data['en']['description'],
                ],
            ]));

            $project->materials()->delete();
            foreach ($data['materials'] as $index => $material) {
                $model = $project->materials()->create([
                    'product_id' => null,
                    'image' => $material['img'],
                    'sort_order' => $index + 1,
                ]);

                $model->translations()->createMany($this->translations([
                    'vi' => ['name' => $material['name_vi'], 'detail' => $material['detail_vi']],
                    'en' => ['name' => $material['name_en'], 'detail' => $material['detail_en']],
                ]));
            }

            $project->images()->delete();
            foreach ($data['gallery'] as $index => $image) {
                $project->images()->create([
                    'image' => $image,
                    'alt' => $data['vi']['name'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}

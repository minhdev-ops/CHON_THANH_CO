<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectMaterial;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
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
                'name' => 'Đường cao tốc Bắc Bắc — Quảng Nam',
                'location' => 'Quảng Nam',
                'description' => "Dự án đường cao tốc Bắc Bắc đoạn qua Quảng Nam là một trong những công trình hạ tầng trọng điểm quốc gia, đòi hỏi tiêu chuẩn kỹ thuật khắt khe về nền móng và độ ổn định lâu dài.\n\nTrong bối cảnh địa chất phức tạp tại khu vực miền Trung, giải pháp gia cố nền đất yếu bằng vật liệu địa kỹ thuật tiên tiến được ưu tiên hàng đầu nhằm đảm bảo an toàn kết cấu và tối ưu chi phí vòng đời dự án.",
                'materials' => [
                    ['name' => 'Lưới địa kỹ thuật', 'detail' => 'Gia cố cốt nền đường, tăng khả năng chịu tải trọng và giảm sụt lún không đều.', 'img' => '/images/products/industrial-1.jpg'],
                    ['name' => 'Vải địa kỹ thuật không dệt', 'detail' => 'Chức năng phân cách và lọc thoát nước bảo vệ nền đường cao tốc.', 'img' => '/images/products/geotextile-roll.jpg'],
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
                'name' => 'Hồ Thuỷ Tiên — Thừa Thiên Huế',
                'location' => 'Thừa Thiên Huế',
                'description' => "Dự án hồ chứa nước Thuỷ Tiên là công trình thuỷ lợi quan trọng phục vụ tưới tiêu cho hàng nghìn hecta đất nông nghiệp tại tỉnh Thừa Thiên Huế.\n\nCHON THANH đã cung cấp giải pháp thảm 3D chống xói mòn và vải địa kỹ thuật cho toàn bộ hệ thống kênh mương và đập chính.",
                'materials' => [
                    ['name' => 'Thảm 3D chống xói mòn', 'detail' => 'Bảo vệ bờ kè và mái dốc hồ chứa khỏi xói mòn do dòng chảy.', 'img' => '/images/products/gabion-1.jpg'],
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
                'name' => 'Khu công nghiệp Hà Nội',
                'location' => 'Hà Nội',
                'description' => "Dự án hạ tầng khu công nghiệp mới tại Hà Nội với quy mô lớn, yêu cầu xử lý nền đất yếu trên diện rộng.\n\nCHON THANH đã cung cấp lưới địa kỹ thuật gia cố nền đường nội bộ và vải địa kỹ thuật phân cách cho toàn bộ hạ tầng giao thông nội khu.",
                'materials' => [
                    ['name' => 'Lưới địa kỹ thuật', 'detail' => 'Gia cố nền đường nội bộ bằng lưới địa kỹ thuật cốt sợi thủy tinh.', 'img' => '/images/products/industrial-1.jpg'],
                    ['name' => 'Vải địa kỹ thuật không dệt', 'detail' => 'Phân cách và lọc thoát nền đường khu công nghiệp.', 'img' => '/images/products/geotextile-roll.jpg'],
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
                'name' => 'Cầu Sông Hàn — Đà Nẵng',
                'location' => 'Đà Nẵng',
                'description' => "Dự án cầu Sông Hàn là công trình giao thông trọng điểm tại thành phố Đà Nẵng, sử dụng lưới địa kỹ thuật cường độ cao để gia cố mố cầu và đường dẫn hai đầu cầu.\n\nCHON THANH đã cung cấp giải pháp gia cố nền móng cho các trụ cầu chính, đảm bảo độ ổn định và chịu tải cho công trình vĩnh cửu này.",
                'materials' => [
                    ['name' => 'Lưới địa kỹ thuật', 'detail' => 'Gia cố mố cầu và nền đường dẫn, tăng khả năng chịu tải.', 'img' => '/images/products/industrial-1.jpg'],
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
                'name' => 'Sân bay quốc tế Long Thành — Đồng Nai',
                'location' => 'Đồng Nai',
                'description' => "Dự án sân bay quốc tế Long Thành là siêu công trình hạ tầng trọng điểm quốc gia, yêu cầu giải pháp gia cố nền và chống thấm đồng bộ cho toàn bộ khu vực đường băng, sân đỗ và nhà ga.\n\nCHON THANH đã cung cấp lưới địa kỹ thuật cường độ cao gia cố nền đường băng, vải địa kỹ thuật phân cách và màng HDPE chống thấm cho hệ thống thoát nước và xử lý môi trường.",
                'materials' => [
                    ['name' => 'Lưới địa kỹ thuật', 'detail' => 'Gia cố nền đường băng sân bay bằng lưới địa kỹ thuật cường độ cao.', 'img' => '/images/products/industrial-1.jpg'],
                    ['name' => 'Màng chống thấm HDPE', 'detail' => 'Chống thấm hệ thống thu gom nước thải và bảo vệ môi trường khu vực sân bay.', 'img' => '/images/products/industrial-1.jpg'],
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
                'name' => 'Khu đô thị xanh — Thủ Đức',
                'location' => 'Thủ Đức, TP.HCM',
                'description' => "Dự án khu đô thị xanh tại Thủ Đức với định hướng phát triển bền vững và hạ tầng kỹ thuật đồng bộ. CHON THANH cung cấp giải pháp địa kỹ thuật toàn diện cho hệ thống đường nội khu, hồ cảnh quan và tường chắn đất.\n\nGiải pháp kết hợp vải địa kỹ thuật phân cách, thảm 3D chống xói mòn mái dốc đảm bảo công trình vận hành ổn định và thân thiện môi trường.",
                'materials' => [
                    ['name' => 'Vải địa kỹ thuật không dệt', 'detail' => 'Phân cách nền đường nội bộ, chống lún cho hạ tầng khu đô thị.', 'img' => '/images/products/geotextile-roll.jpg'],
                    ['name' => 'Thảm 3D chống xói mòn', 'detail' => 'Bảo vệ mái dốc và hồ cảnh quan khỏi xói mòn do mưa và dòng chảy.', 'img' => '/images/products/gabion-1.jpg'],
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
                    'name' => $data['name'],
                    'location' => $data['location'],
                    'description' => $data['description'],
                ]
            );

            $project->materials()->delete();
            foreach ($data['materials'] as $index => $material) {
                ProjectMaterial::create([
                    'project_id' => $project->id,
                    'image' => $material['img'],
                    'name' => $material['name'],
                    'detail' => $material['detail'],
                    'sort_order' => $index + 1,
                ]);
            }

            $project->images()->delete();
            foreach ($data['gallery'] as $index => $image) {
                $project->images()->create([
                    'image' => $image,
                    'alt' => $data['name'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
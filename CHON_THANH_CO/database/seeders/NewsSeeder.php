<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'tin-tuc', 'sort_order' => 1, 'name' => 'Tin tức'],
            ['slug' => 'ky-thuat', 'sort_order' => 2, 'name' => 'Kỹ thuật'],
            ['slug' => 'su-kien', 'sort_order' => 3, 'name' => 'Sự kiện'],
            ['slug' => 'du-an', 'sort_order' => 4, 'name' => 'Dự án'],
        ];

        foreach ($categories as $data) {
            NewsCategory::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order'], 'name' => $data['name']]
            );
        }

        $categoryIds = NewsCategory::pluck('id', 'slug');

        $articles = [
            [
                'slug' => 'giai-phap-gia-co-nen-dat-yeu',
                'category' => 'ky-thuat',
                'image' => '/images/products/industrial-1.jpg',
                'published_at' => '2024-12-15',
                'title' => 'Giải pháp gia cố nền đất yếu bằng vải địa kỹ thuật',
                'excerpt' => 'Tìm hiểu các giải pháp gia cố nền đất yếu tiên tiến nhất hiện nay sử dụng vải địa kỹ thuật không dệt và dệt.',
                'content' => "Nền đất yếu luôn là thách thức lớn trong các công trình hạ tầng giao thông tại Việt Nam. Vải địa kỹ thuật đã chứng minh hiệu quả vượt trội trong việc phân tán tải trọng, tăng khả năng chịu lực và rút ngắn thời gian thi công.\n\nVải địa kỹ thuật không dệt (non-woven) được sử dụng phổ biến trong các lớp đệm phân cách, lọc ngược và tiêu thoát nước, trong khi vải dệt (woven) với cường độ chịu kéo cao phù hợp cho các lớp gia cố chịu lực.\n\nKhi lựa chọn giải pháp, kỹ sư cần căn cứ vào điều kiện địa chất, tải trọng thiết kế và tiêu chuẩn áp dụng như TCVN 9844:2013 để đảm bảo hiệu quả kinh tế - kỹ thuật tối ưu cho từng dự án.",
            ],
            [
                'slug' => 'ung-dung-tham-3d-chong-xoi-mon',
                'category' => 'du-an',
                'image' => '/images/products/gabion-1.jpg',
                'published_at' => '2024-11-20',
                'title' => 'Ứng dụng thảm 3D trong chống xói mòn bờ sông',
                'excerpt' => 'Thảm 3D chống xói mòn là giải pháp hiệu quả cho các công trình bảo vệ bờ sông, mái dốc và kênh mương.',
                'content' => null,
            ],
            [
                'slug' => 'chon-thanh-dat-doi-tac-hock',
                'category' => 'tin-tuc',
                'image' => '/images/news/workshop.jpg',
                'published_at' => '2024-10-05',
                'title' => 'CHON THANH đạt chứng nhận đối tác uỷ quyền HOCK Technology',
                'excerpt' => 'CHON THANH vinh dự được công nhận là nhà phân phối uỷ quyền chính thức của HOCK Technology tại thị trường Việt Nam.',
                'content' => null,
            ],
            [
                'slug' => 'tieu-chuan-iso-9001-2015',
                'category' => 'ky-thuat',
                'image' => '/images/products/geotextile-roll.jpg',
                'published_at' => '2024-09-18',
                'title' => 'Tiêu chuẩn ISO 9001:2015 trong quản lý chất lượng vật liệu địa kỹ thuật',
                'excerpt' => 'ISO 9001:2015 là tiêu chuẩn quốc tế về hệ thống quản lý chất lượng mà CHON THANH áp dụng.',
                'content' => null,
            ],
            [
                'slug' => 'huong-dan-thi-cong-vai-dia-ky-thuat',
                'category' => 'ky-thuat',
                'image' => '/images/news/construction-guide.jpg',
                'published_at' => '2024-08-10',
                'title' => 'Hướng dẫn thi công vải địa kỹ thuật đúng kỹ thuật',
                'excerpt' => 'Quy trình thi công vải địa kỹ thuật đạt chuẩn: từ khâu chuẩn bị mặt bằng, trải thảm, đến nghiệm thu và bảo dưỡng.',
                'content' => null,
            ],
            [
                'slug' => 'hoi-thao-ung-dung-geogrid-2024',
                'category' => 'su-kien',
                'image' => '/images/news/workshop.jpg',
                'published_at' => '2024-07-20',
                'title' => 'Hội thảo ứng dụng lưới địa kỹ thuật trong xây dựng hạ tầng',
                'excerpt' => 'CHON THANH tổ chức hội thảo chuyên đề về ứng dụng lưới địa kỹ thuật cốt sợi thủy tinh trong gia cố mặt đường bê tông nhựa.',
                'content' => null,
            ],
        ];

        foreach ($articles as $data) {
            News::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'news_category_id' => $categoryIds[$data['category']] ?? null,
                    'image' => $data['image'],
                    'published_at' => $data['published_at'],
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                ]
            );
        }
    }
}
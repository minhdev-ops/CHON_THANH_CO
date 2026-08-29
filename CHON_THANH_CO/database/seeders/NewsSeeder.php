<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $categories = [
            ['slug' => 'tin-tuc', 'sort_order' => 1, 'vi' => 'Tin tức', 'en' => 'News'],
            ['slug' => 'ky-thuat', 'sort_order' => 2, 'vi' => 'Kỹ thuật', 'en' => 'Technical'],
            ['slug' => 'su-kien', 'sort_order' => 3, 'vi' => 'Sự kiện', 'en' => 'Events'],
            ['slug' => 'du-an', 'sort_order' => 4, 'vi' => 'Dự án', 'en' => 'Projects'],
        ];

        foreach ($categories as $data) {
            $category = NewsCategory::updateOrCreate(
                ['slug' => $data['slug']],
                ['sort_order' => $data['sort_order']]
            );

            $category->translations()->delete();
            $category->translations()->createMany($this->translations([
                'vi' => ['name' => $data['vi']],
                'en' => ['name' => $data['en']],
            ]));
        }

        $categoryIds = NewsCategory::pluck('id', 'slug');

        $articles = [
            [
                'slug' => 'giai-phap-gia-co-nen-dat-yeu',
                'category' => 'ky-thuat',
                'image' => '/images/products/industrial-1.jpg',
                'published_at' => '2024-12-15',
                'vi' => [
                    'title' => 'Giải pháp gia cố nền đất yếu bằng vải địa kỹ thuật',
                    'excerpt' => 'Tìm hiểu các giải pháp gia cố nền đất yếu tiên tiến nhất hiện nay sử dụng vải địa kỹ thuật không dệt và dệt.',
                    'content' => "Nền đất yếu luôn là thách thức lớn trong các công trình hạ tầng giao thông tại Việt Nam. Vải địa kỹ thuật đã chứng minh hiệu quả vượt trội trong việc phân tán tải trọng, tăng khả năng chịu lực và rút ngắn thời gian thi công.\n\nVải địa kỹ thuật không dệt (non-woven) được sử dụng phổ biến trong các lớp đệm phân cách, lọc ngược và tiêu thoát nước, trong khi vải dệt (woven) với cường độ chịu kéo cao phù hợp cho các lớp gia cố chịu lực.\n\nKhi lựa chọn giải pháp, kỹ sư cần căn cứ vào điều kiện địa chất, tải trọng thiết kế và tiêu chuẩn áp dụng như TCVN 9844:2013 để đảm bảo hiệu quả kinh tế - kỹ thuật tối ưu cho từng dự án.",
                ],
                'en' => [
                    'title' => 'Soft soil reinforcement solutions with geotextiles',
                    'excerpt' => 'Discover the most advanced soft soil reinforcement solutions using non-woven and woven geotextiles.',
                    'content' => "Soft soil has always been a major challenge in infrastructure projects across Vietnam. Geotextiles have proven to be highly effective in distributing loads, increasing bearing capacity and shortening construction time.\n\nNon-woven geotextiles are commonly used for separation, filtration and drainage layers, while woven geotextiles with high tensile strength are suitable for load-bearing reinforcement layers.\n\nWhen selecting a solution, engineers should consider the geological conditions, design loads and applicable standards such as TCVN 9844:2013 to ensure the optimal technical and economic efficiency for each project.",
                ],
            ],
            [
                'slug' => 'ung-dung-tham-3d-chong-xoi-mon',
                'category' => 'du-an',
                'image' => '/images/products/gabion-1.jpg',
                'published_at' => '2024-11-20',
                'vi' => [
                    'title' => 'Ứng dụng thảm 3D trong chống xói mòn bờ sông',
                    'excerpt' => 'Thảm 3D chống xói mòn là giải pháp hiệu quả cho các công trình bảo vệ bờ sông, mái dốc và kênh mương.',
                ],
                'en' => [
                    'title' => 'Applying 3D mats for riverbank erosion control',
                    'excerpt' => '3D erosion control mats are an effective solution for riverbank, slope and channel protection works.',
                ],
            ],
            [
                'slug' => 'chon-thanh-dat-doi-tac-hock',
                'category' => 'tin-tuc',
                'image' => '/images/news/workshop.jpg',
                'published_at' => '2024-10-05',
                'vi' => [
                    'title' => 'CHON THANH đạt chứng nhận đối tác uỷ quyền HOCK Technology',
                    'excerpt' => 'CHON THANH vinh dự được công nhận là nhà phân phối uỷ quyền chính thức của HOCK Technology tại thị trường Việt Nam.',
                ],
                'en' => [
                    'title' => 'CHON THANH becomes authorized partner of HOCK Technology',
                    'excerpt' => 'CHON THANH is honored to be recognized as the official authorized distributor of HOCK Technology in Vietnam.',
                ],
            ],
            [
                'slug' => 'tieu-chuan-iso-9001-2015',
                'category' => 'ky-thuat',
                'image' => '/images/products/geotextile-roll.jpg',
                'published_at' => '2024-09-18',
                'vi' => [
                    'title' => 'Tiêu chuẩn ISO 9001:2015 trong quản lý chất lượng vật liệu địa kỹ thuật',
                    'excerpt' => 'ISO 9001:2015 là tiêu chuẩn quốc tế về hệ thống quản lý chất lượng mà CHON THANH áp dụng.',
                ],
                'en' => [
                    'title' => 'ISO 9001:2015 in geosynthetic material quality management',
                    'excerpt' => 'ISO 9001:2015 is the international quality management standard adopted by CHON THANH.',
                ],
            ],
            [
                'slug' => 'huong-dan-thi-cong-vai-dia-ky-thuat',
                'category' => 'ky-thuat',
                'image' => '/images/news/construction-guide.jpg',
                'published_at' => '2024-08-10',
                'vi' => [
                    'title' => 'Hướng dẫn thi công vải địa kỹ thuật đúng kỹ thuật',
                    'excerpt' => 'Quy trình thi công vải địa kỹ thuật đạt chuẩn: từ khâu chuẩn bị mặt bằng, trải thảm, đến nghiệm thu và bảo dưỡng.',
                ],
                'en' => [
                    'title' => 'Proper geotextile installation guide',
                    'excerpt' => 'Standard geotextile installation procedure: from site preparation and unrolling to acceptance and maintenance.',
                ],
            ],
            [
                'slug' => 'hoi-thao-ung-dung-geogrid-2024',
                'category' => 'su-kien',
                'image' => '/images/news/workshop.jpg',
                'published_at' => '2024-07-20',
                'vi' => [
                    'title' => 'Hội thảo ứng dụng lưới địa kỹ thuật trong xây dựng hạ tầng',
                    'excerpt' => 'CHON THANH tổ chức hội thảo chuyên đề về ứng dụng lưới địa kỹ thuật cốt sợi thủy tinh trong gia cố mặt đường bê tông nhựa.',
                ],
                'en' => [
                    'title' => 'Seminar on geogrid applications in infrastructure',
                    'excerpt' => 'CHON THANH held a seminar on fiberglass geogrid applications in asphalt pavement reinforcement.',
                ],
            ],
        ];

        foreach ($articles as $data) {
            $news = News::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'news_category_id' => $categoryIds[$data['category']] ?? null,
                    'image' => $data['image'],
                    'published_at' => $data['published_at'],
                ]
            );

            $news->translations()->delete();
            $news->translations()->createMany($this->translations([
                'vi' => [
                    'title' => $data['vi']['title'],
                    'excerpt' => $data['vi']['excerpt'],
                    'content' => $data['vi']['content'] ?? null,
                ],
                'en' => [
                    'title' => $data['en']['title'],
                    'excerpt' => $data['en']['excerpt'],
                    'content' => $data['en']['content'] ?? null,
                ],
            ]));
        }
    }
}

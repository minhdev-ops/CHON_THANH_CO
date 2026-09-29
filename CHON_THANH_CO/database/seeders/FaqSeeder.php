<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'sort_order' => 1,
                'question' => 'CHON THANH cung cấp những loại vật liệu địa kỹ thuật nào?',
                'answer' => 'Chúng tôi cung cấp đầy đủ các dòng sản phẩm: vải địa kỹ thuật không dệt (ART 12 – ART 280), vải địa kỹ thuật dệt (GET 5 – GET 500), lưới địa kỹ thuật, thảm 3D chống xói mòn, rọ đá, băng thấm GCL, màng chống thấm HDPE, lưới thép B40 và dây kẽm gai.',
            ],
            [
                'sort_order' => 2,
                'question' => 'Làm thế nào để nhận báo giá sản phẩm?',
                'answer' => 'Quý khách vui lòng điền thông tin vào form liên hệ hoặc gọi hotline 0909 292 530. Đội ngũ kỹ sư của chúng tôi sẽ tư vấn và gửi báo giá trong vòng 24 giờ.',
            ],
            [
                'sort_order' => 3,
                'question' => 'Thời gian giao hàng trung bình là bao lâu?',
                'answer' => 'Chúng tôi cam kết thời gian giao hàng tối đa 7 ngày trên toàn quốc đối với các sản phẩm có sẵn trong kho, bằng đội xe tải 2.5–18 tấn đến tận chân công trình.',
            ],
            [
                'sort_order' => 4,
                'question' => 'CHON THANH có chứng nhận chất lượng không?',
                'answer' => 'Tất cả sản phẩm của CHON THANH đều có chứng nhận CO/CQ đầy đủ. Công ty đạt chứng nhận ISO 9001:2015, sản phẩm phù hợp TCVN 9844:2013 và là nhà phân phối uỷ quyền chính thức của HOCK Technology.',
            ],
            [
                'sort_order' => 5,
                'question' => 'Tôi có thể đến tham quan kho bãi và văn phòng không?',
                'answer' => 'Hoàn toàn có thể. Quý khách vui lòng liên hệ trước để đặt lịch hẹn, đội ngũ chúng tôi sẽ sẵn sàng đón tiếp tại 416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh.',
            ],
            [
                'sort_order' => 6,
                'question' => 'CHON THANH có hỗ trợ vận chuyển hàng đến công trình không?',
                'answer' => 'Chúng tôi hỗ trợ vận chuyển đến chân công trình trên toàn quốc và xuất khẩu sang Campuchia, Lào, Myanmar. Chi phí vận chuyển được tính toán dựa trên khối lượng đơn hàng và khoảng cách địa lý.',
            ],
            [
                'sort_order' => 7,
                'question' => 'Các sản phẩm của CHON THANH có bảo hành không?',
                'answer' => 'Tất cả sản phẩm đều được bảo hành theo tiêu chuẩn của nhà sản xuất, thông thường từ 12–24 tháng kể từ ngày giao hàng, tuỳ theo từng dòng sản phẩm cụ thể.',
            ],
        ];

        foreach ($faqs as $data) {
            Faq::create($data);
        }
    }
}
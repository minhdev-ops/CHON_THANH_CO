<?php

namespace Database\Seeders;

use App\Models\Faq;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $faqs = [
            [
                'sort_order' => 1,
                'vi' => [
                    'question' => 'CHON THANH cung cấp những loại vật liệu địa kỹ thuật nào?',
                    'answer' => 'Chúng tôi cung cấp đầy đủ các dòng sản phẩm: vải địa kỹ thuật không dệt (ART 12 – ART 280), vải địa kỹ thuật dệt (GET 5 – GET 500), lưới địa kỹ thuật, thảm 3D chống xói mòn, rọ đá, băng thấm GCL, màng chống thấm HDPE, lưới thép B40 và dây kẽm gai.',
                ],
                'en' => [
                    'question' => 'What types of geosynthetic materials does CHON THANH supply?',
                    'answer' => 'We supply the full range: non-woven geotextiles (ART 12 – ART 280), woven geotextiles (GET 5 – GET 500), geogrids, 3D erosion control mats, gabions, GCLs, HDPE geomembranes, B40 wire mesh and barbed wire.',
                ],
            ],
            [
                'sort_order' => 2,
                'vi' => [
                    'question' => 'Làm thế nào để nhận báo giá sản phẩm?',
                    'answer' => 'Quý khách vui lòng điền thông tin vào form liên hệ hoặc gọi hotline 0909 292 530. Đội ngũ kỹ sư của chúng tôi sẽ tư vấn và gửi báo giá trong vòng 24 giờ.',
                ],
                'en' => [
                    'question' => 'How can I get a product quotation?',
                    'answer' => 'Please fill in the contact form or call hotline 0909 292 530. Our engineers will advise and send you a quotation within 24 hours.',
                ],
            ],
            [
                'sort_order' => 3,
                'vi' => [
                    'question' => 'Thời gian giao hàng trung bình là bao lâu?',
                    'answer' => 'Chúng tôi cam kết thời gian giao hàng tối đa 7 ngày trên toàn quốc đối với các sản phẩm có sẵn trong kho, bằng đội xe tải 2.5–18 tấn đến tận chân công trình.',
                ],
                'en' => [
                    'question' => 'What is the average delivery time?',
                    'answer' => 'We commit to a maximum delivery time of 7 days nationwide for in-stock products, delivered to site by our fleet of 2.5–18 ton trucks.',
                ],
            ],
            [
                'sort_order' => 4,
                'vi' => [
                    'question' => 'CHON THANH có chứng nhận chất lượng không?',
                    'answer' => 'Tất cả sản phẩm của CHON THANH đều có chứng nhận CO/CQ đầy đủ. Công ty đạt chứng nhận ISO 9001:2015, sản phẩm phù hợp TCVN 9844:2013 và là nhà phân phối uỷ quyền chính thức của HOCK Technology.',
                ],
                'en' => [
                    'question' => 'Does CHON THANH have quality certifications?',
                    'answer' => 'All CHON THANH products come with full CO/CQ certificates. The company is ISO 9001:2015 certified, products conform to TCVN 9844:2013, and we are the official authorized distributor of HOCK Technology.',
                ],
            ],
            [
                'sort_order' => 5,
                'vi' => [
                    'question' => 'Tôi có thể đến tham quan kho bãi và văn phòng không?',
                    'answer' => 'Hoàn toàn có thể. Quý khách vui lòng liên hệ trước để đặt lịch hẹn, đội ngũ chúng tôi sẽ sẵn sàng đón tiếp tại 416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh.',
                ],
                'en' => [
                    'question' => 'Can I visit your warehouse and office?',
                    'answer' => 'Absolutely. Please contact us in advance to book an appointment. We will be happy to welcome you at 416A CC2 Street, Son Ky Ward, Tan Phu District, Ho Chi Minh City.',
                ],
            ],
            [
                'sort_order' => 6,
                'vi' => [
                    'question' => 'CHON THANH có hỗ trợ vận chuyển hàng đến công trình không?',
                    'answer' => 'Chúng tôi hỗ trợ vận chuyển đến chân công trình trên toàn quốc và xuất khẩu sang Campuchia, Lào, Myanmar. Chi phí vận chuyển được tính toán dựa trên khối lượng đơn hàng và khoảng cách địa lý.',
                ],
                'en' => [
                    'question' => 'Does CHON THANH support delivery to site?',
                    'answer' => 'We support delivery to site nationwide and exports to Cambodia, Laos and Myanmar. Transport costs are calculated based on order volume and distance.',
                ],
            ],
            [
                'sort_order' => 7,
                'vi' => [
                    'question' => 'Các sản phẩm của CHON THANH có bảo hành không?',
                    'answer' => 'Tất cả sản phẩm đều được bảo hành theo tiêu chuẩn của nhà sản xuất, thông thường từ 12–24 tháng kể từ ngày giao hàng, tuỳ theo từng dòng sản phẩm cụ thể.',
                ],
                'en' => [
                    'question' => 'Do CHON THANH products come with a warranty?',
                    'answer' => 'All products are warranted according to manufacturer standards, typically 12–24 months from delivery date depending on the product line.',
                ],
            ],
        ];

        foreach ($faqs as $data) {
            $faq = Faq::create([
                'sort_order' => $data['sort_order'],
            ]);

            $faq->translations()->createMany($this->translations([
                'vi' => $data['vi'],
                'en' => $data['en'],
            ]));
        }
    }
}

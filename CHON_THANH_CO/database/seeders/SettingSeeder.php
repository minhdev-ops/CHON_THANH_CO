<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ===== Công ty =====
            'company.name_vi' => 'CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH',
            'company.name_en' => 'CHON THANH COMMERCIAL AND SERVICE CO., LTD',
            'company.short_name' => 'CHON THANH CO.',
            'company.tax_code' => '0303792837',
            'company.established' => '11/05/2005',
            'company.capital' => '20.000.000.000 VNĐ',
            'company.director' => 'Ông Hoàng Đức Thọ',
            'company.staff' => '18',
            'company.address' => '416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh',
            'company.phone' => '0909 292 530',
            'company.fax' => '028.38 165 235',
            'company.email' => 'chonthanhco@gmail.com',
            'company.website' => 'chonthanh.com.vn',
            'company.partner' => 'HOCK Technology Co.,Ltd',
            'company.brand' => 'ARITEX',
            'company.industries' => '31 ngành nghề kinh doanh',
            'company.description' => 'Nhà phân phối chính thức vật liệu địa kỹ thuật ARITEX và các sản phẩm gia cố nền, chống thấm, chống xói mòn phục vụ hạ tầng giao thông, thủy lợi và bảo vệ môi trường tại Việt Nam và xuất khẩu.',
            'company.capability_file' => '',
            'company.inspection_file' => '',
            'company.factories' => json_encode([
                ['name' => 'Nhà máy CN1 - Rọ đá Á Châu', 'address' => '30 Tân Thới Nhì, Xã Tân Thới Nhì, H. Hóc Môn, TP.HCM', 'product' => 'Rọ đá, thả đá', 'capacity' => '3,000 tấn/năm (từ 2014)'],
                ['name' => 'Nhà máy CN2 - Lưới Thép Tiên Phong', 'address' => '106 Hoàng Văn Thái, P. Hòa Khánh Nam, Q. Liên Chiểu, Đà Nẵng', 'product' => 'Lưới thép B40, lưới thép hàn, dây kẽm gai', 'capacity' => '5,000 tấn/năm (từ 2017)'],
            ], JSON_UNESCAPED_UNICODE),

            // ===== Liên hệ =====
            'contact.address' => '416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh',
            'contact.phone' => '0909 292 530',
            'contact.fax' => '028.38 165 235',
            'contact.email' => 'chonthanhco@gmail.com',
            'contact.website' => 'chonthanh.com.vn',
            'contact.contact_email' => env('ADMIN_EMAIL', 'admin@chonthanhco.com'),
            'contact.map_embed' => '',
            'contact.working_hours' => 'Thứ 2 - Thứ 6: 08:30 - 17:30',

            // ===== Mạng xã hội / liên kết =====
            'social.facebook' => '',
            'social.zalo' => 'https://zalo.me/0909292530',
            'social.messenger' => '',
            'social.ggmap' => '',

            // ===== SEO =====
            'seo.default_title' => 'CHON THANH CO. - Vật liệu địa kỹ thuật, gia cố nền, chống thấm',
            'seo.default_description' => 'CHON THANH cung cấp vải địa kỹ thuật không dệt, vải dệt, lưới địa kỹ thuật, thảm 3D, rọ đá, màng HDPE theo tiêu chuẩn TCVN 9844:2013, ISO 9001:2015.',

            // ===== Trang giới thiệu =====
            'about.history_vi' => "CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH hoạt động từ năm 2005, là nhà phân phối uỷ quyền chính thức của HOCK Technology cho dòng vải địa kỹ thuật ARITEX, cùng hai nhà máy sản xuất rọ đá và lưới thép tại TP.HCM và Đà Nẵng.\n\nVới hơn " . (date('Y') - 2005) . " năm kinh nghiệm, công suất 8.000 tấn/năm và đội ngũ 18 nhân sự, chúng tôi không chỉ cung cấp vật tư mà còn mang đến sự an tâm tuyệt đối cho kỹ sư và chủ đầu tư thông qua cam kết chất lượng ISO 9001:2015, sản phẩm hợp chuẩn TCVN 9844:2013 và tiêu chuẩn châu Âu.\n\nĐội ngũ chuyên gia của Chơn Thành luôn sẵn sàng đồng hành cùng dự án từ giai đoạn thiết kế cơ sở đến khi hoàn thiện, đảm bảo tính bền vững và tối ưu hoá chi phí đầu tư.",
            'about.history_en' => "CHON THANH SERVICE & TRADING COMPANY LIMITED has been operating since 2005 and is the official authorized distributor of HOCK Technology for the ARITEX geotextile line, together with two factories producing gabions and steel wire mesh in Ho Chi Minh City and Da Nang.\n\nWith over " . (date('Y') - 2005) . " years of experience, an annual capacity of 8,000 tons and a team of 18 professionals, we not only supply materials but also provide complete peace of mind for engineers and investors through our ISO 9001:2015 quality commitment, TCVN 9844:2013 compliant products and European standards.\n\nThe Chon Thanh expert team is always ready to accompany your project from the concept design stage to completion, ensuring sustainability and optimized investment costs.",
            'about.mission_vi' => 'Cung cấp giải pháp địa kỹ thuật chất lượng cao, giá cạnh tranh cho ngành xây dựng Việt Nam. Đảm bảo mọi công trình đều được bảo vệ bởi nền tảng vật liệu vững chắc nhất.',
            'about.mission_en' => 'Provide high-quality, competitively priced geotechnical solutions for the Vietnamese construction industry. Ensure every project is protected by the most solid material foundation.',
            'about.vision_vi' => 'Trở thành nhà phân phối vật liệu địa kỹ thuật hàng đầu Đông Nam Á, tiên phong ứng dụng công nghệ vật liệu mới, thân thiện với môi trường vào các dự án hạ tầng cốt lõi.',
            'about.vision_en' => 'Become the leading geosynthetic material distributor in Southeast Asia, pioneering the application of new, environmentally friendly material technology in core infrastructure projects.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => explode('.', $key)[0]]
            );
        }
    }
}

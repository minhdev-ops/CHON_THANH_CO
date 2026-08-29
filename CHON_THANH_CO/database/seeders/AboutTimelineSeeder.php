<?php

namespace Database\Seeders;

use App\Models\AboutTimeline;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class AboutTimelineSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        // Xóa hết dữ liệu cũ để seeder luôn tái tạo đúng 6 mốc gốc
        // (tránh trùng lặp khi admin đã đổi thứ tự sort_order trước đó)
        AboutTimeline::query()->forceDelete();

        $items = [
            [
                'sort_order' => 1,
                'vi' => ['year' => '2005 - Thành lập', 'description' => 'Thành lập với tên gọi CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837 do Sở KH&ĐT TP.HCM cấp.'],
                'en' => ['year' => '2005 - Founding', 'description' => 'Founded as CHON THANH SERVICE & TRADING COMPANY LIMITED, enterprise code 0303792837 issued by the HCMC Department of Planning and Investment.'],
            ],
            [
                'sort_order' => 2,
                'vi' => ['year' => '2007 - Nhà máy Rọ đá Á Châu', 'description' => 'Khánh thành nhà máy sản xuất rọ đá, thả đá tại Hóc Môn, TP.HCM - khởi đầu chuỗi sản xuất của công ty.'],
                'en' => ['year' => '2007 - Asia Gabion Factory', 'description' => 'Commissioned the gabion and rip-rap production factory in Hoc Mon, Ho Chi Minh City - the start of the company production chain.'],
            ],
            [
                'sort_order' => 3,
                'vi' => ['year' => '2013 - Hợp chuẩn TCVN 9844', 'description' => 'Sản phẩm vải địa kỹ thuật đạt chứng nhận hợp chuẩn TCVN 9844:2013 và tiêu chuẩn châu Âu.'],
                'en' => ['year' => '2013 - TCVN 9844 certification', 'description' => 'Geotechnical products achieved TCVN 9844:2013 conformity certification and European standards.'],
            ],
            [
                'sort_order' => 4,
                'vi' => ['year' => '2020 - Đối tác HOCK Technology', 'description' => 'Trở thành nhà phân phối uỷ quyền chính thức dòng sản phẩm vải địa kỹ thuật ARITEX của HOCK Technology tại Việt Nam.'],
                'en' => ['year' => '2020 - HOCK Technology partner', 'description' => 'Became the official authorized distributor of HOCK Technology for the ARITEX geotextile product line in Vietnam.'],
            ],
            [
                'sort_order' => 5,
                'vi' => ['year' => '2023 - Chứng nhận ISO 9001:2015', 'description' => 'Hệ thống quản lý chất lượng đạt chuẩn ISO 9001:2015 do NQA (UKAS - Vương quốc Anh) chứng nhận.'],
                'en' => ['year' => '2023 - ISO 9001:2015 certification', 'description' => 'Quality management system certified ISO 9001:2015 by NQA (UKAS - United Kingdom).'],
            ],
            [
                'sort_order' => 6,
                'vi' => ['year' => 'Hiện tại', 'description' => 'Hai nhà máy, công suất 8.000 tấn/năm, đồng hành cùng hàng nghìn công trình hạ tầng trên toàn quốc và xuất khẩu.'],
                'en' => ['year' => 'Today', 'description' => 'Two factories, capacity of 8,000 tons/year, accompanying thousands of infrastructure projects nationwide and exports.'],
            ],
        ];

        foreach ($items as $data) {
            $item = AboutTimeline::updateOrCreate(
                ['sort_order' => $data['sort_order']],
                ['is_active' => true]
            );

            $item->translations()->delete();
            $item->translations()->createMany($this->translations([
                'vi' => $data['vi'],
                'en' => $data['en'],
            ]));
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\AboutTimeline;
use Illuminate\Database\Seeder;

class AboutTimelineSeeder extends Seeder
{
    public function run(): void
    {
        AboutTimeline::query()->forceDelete();

        $items = [
            [
                'sort_order' => 1,
                'year' => '2005 - Thành lập',
                'description' => 'Thành lập với tên gọi CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837 do Sở KH&ĐT TP.HCM cấp.',
            ],
            [
                'sort_order' => 2,
                'year' => '2007 - Nhà máy Rọ đá Á Châu',
                'description' => 'Khánh thành nhà máy sản xuất rọ đá, thả đá tại Hóc Môn, TP.HCM - khởi đầu chuỗi sản xuất của công ty.',
            ],
            [
                'sort_order' => 3,
                'year' => '2013 - Hợp chuẩn TCVN 9844',
                'description' => 'Sản phẩm vải địa kỹ thuật đạt chứng nhận hợp chuẩn TCVN 9844:2013 và tiêu chuẩn châu Âu.',
            ],
            [
                'sort_order' => 4,
                'year' => '2020 - Đối tác HOCK Technology',
                'description' => 'Trở thành nhà phân phối uỷ quyền chính thức dòng sản phẩm vải địa kỹ thuật ARITEX của HOCK Technology tại Việt Nam.',
            ],
            [
                'sort_order' => 5,
                'year' => '2023 - Chứng nhận ISO 9001:2015',
                'description' => 'Hệ thống quản lý chất lượng đạt chuẩn ISO 9001:2015 do NQA (UKAS - Vương quốc Anh) chứng nhận.',
            ],
            [
                'sort_order' => 6,
                'year' => 'Hiện tại',
                'description' => 'Hai nhà máy, công suất 8.000 tấn/năm, đồng hành cùng hàng nghìn công trình hạ tầng trên toàn quốc và xuất khẩu.',
            ],
        ];

        foreach ($items as $data) {
            AboutTimeline::updateOrCreate(
                ['sort_order' => $data['sort_order']],
                ['year' => $data['year'], 'description' => $data['description'], 'is_active' => true]
            );
        }
    }
}
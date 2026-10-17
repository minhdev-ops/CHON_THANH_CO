<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $certificates = [
            [
                'slug' => 'giay-uy-quyen-hock',
                'image' => '/images/certificates/authorization-hock.jpg',
                'sort_order' => 1,
                'name' => 'Giấy uỷ quyền phân phối HOCK',
                'description' => "Giấy uỷ quyền số CTCO-AT-VN04-20220905-01 cấp ngày 30/10/2023. CHON THANH là nhà phân phối uỷ quyền chính thức của HOCK Technology Co.,Ltd cho dòng sản phẩm vải địa kỹ thuật ARITEX tại thị trường Việt Nam.",
            ],
            [
                'slug' => 'iso-9001-2015',
                'image' => '/images/certificates/iso-9001.jpg',
                'sort_order' => 2,
                'name' => 'Chứng nhận ISO 9001:2015',
                'description' => "Hệ thống quản lý chất lượng đạt chuẩn ISO 9001:2015, chứng nhận số 120895 do NQA chứng nhận (UKAS - Vương quốc Anh), hiệu lực 13/09/2023 – 13/09/2026. Áp dụng cho hoạt động sản xuất và phân phối vật liệu địa kỹ thuật.",
            ],
            [
                'slug' => 'giay-chung-nhan-dang-ky-kinh-doanh',
                'image' => '/images/certificates/co-cq.jpg',
                'sort_order' => 3,
                'name' => 'Giấy chứng nhận đăng ký kinh doanh',
                'description' => "CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837, đăng ký lần đầu 11/05/2005, đăng ký thay đổi lần thứ 15 ngày 15/10/2025 do Sở Kế hoạch và Đầu tư TP.HCM cấp. Vốn điều lệ 20.000.000.000 đồng.",
            ],
            [
                'slug' => 'hop-chuan-tcvn-9844',
                'image' => '/images/certificates/hop-chuan.jpg',
                'sort_order' => 4,
                'name' => 'Giấy chứng nhận hợp chuẩn TCVN 9844:2013',
                'description' => "Sản phẩm vải địa kỹ thuật phù hợp tiêu chuẩn quốc gia TCVN 9844:2013 về vải địa kỹ thuật - phương pháp thử và yêu cầu kỹ thuật, đồng thời đáp ứng các tiêu chuẩn tương đương của châu Âu (EN ISO 10319).",
            ],
        ];

        foreach ($certificates as $data) {
            Certificate::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'image' => $data['image'],
                    'sort_order' => $data['sort_order'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]
            );
        }
    }
}
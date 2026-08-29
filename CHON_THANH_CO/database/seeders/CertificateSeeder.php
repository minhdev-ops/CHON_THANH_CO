<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Database\Seeders\Concerns\SeedsTranslations;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        $certificates = [
            [
                'slug' => 'giay-uy-quyen-hock',
                'image' => '/images/certificates/authorization-hock.jpg',
                'sort_order' => 1,
                'vi' => [
                    'name' => 'Giấy uỷ quyền phân phối HOCK',
                    'description' => "Giấy uỷ quyền số CTCO-AT-VN04-20220905-01 cấp ngày 30/10/2023. CHON THANH là nhà phân phối uỷ quyền chính thức của HOCK Technology Co.,Ltd cho dòng sản phẩm vải địa kỹ thuật ARITEX tại thị trường Việt Nam.",
                ],
                'en' => [
                    'name' => 'HOCK Authorized Distributor Letter',
                    'description' => "Authorization letter no. CTCO-AT-VN04-20220905-01 issued on 30/10/2023. CHON THANH is the official authorized distributor of HOCK Technology Co.,Ltd for ARITEX geotextile products in Vietnam.",
                ],
            ],
            [
                'slug' => 'iso-9001-2015',
                'image' => '/images/certificates/iso-9001.jpg',
                'sort_order' => 2,
                'vi' => [
                    'name' => 'Chứng nhận ISO 9001:2015',
                    'description' => "Hệ thống quản lý chất lượng đạt chuẩn ISO 9001:2015, chứng nhận số 120895 do NQA chứng nhận (UKAS - Vương quốc Anh), hiệu lực 13/09/2023 – 13/09/2026. Áp dụng cho hoạt động sản xuất và phân phối vật liệu địa kỹ thuật.",
                ],
                'en' => [
                    'name' => 'ISO 9001:2015 Certificate',
                    'description' => "Quality management system certified to ISO 9001:2015, certificate no. 120895 issued by NQA (UKAS - United Kingdom), valid 13/09/2023 – 13/09/2026. Applies to the production and distribution of geosynthetic materials.",
                ],
            ],
            [
                'slug' => 'giay-chung-nhan-dang-ky-kinh-doanh',
                'image' => '/images/certificates/co-cq.jpg',
                'sort_order' => 3,
                'vi' => [
                    'name' => 'Giấy chứng nhận đăng ký kinh doanh',
                    'description' => "CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837, đăng ký lần đầu 11/05/2005, đăng ký thay đổi lần thứ 15 ngày 15/10/2025 do Sở Kế hoạch và Đầu tư TP.HCM cấp. Vốn điều lệ 20.000.000.000 đồng.",
                ],
                'en' => [
                    'name' => 'Business Registration Certificate',
                    'description' => "CHON THANH COMMERCIAL AND SERVICE CO., LTD, enterprise code 0303792837, first registered on 11/05/2005, 15th amendment on 15/10/2025, issued by the Department of Planning and Investment of Ho Chi Minh City. Charter capital VND 20,000,000,000.",
                ],
            ],
            [
                'slug' => 'hop-chuan-tcvn-9844',
                'image' => '/images/certificates/hop-chuan.jpg',
                'sort_order' => 4,
                'vi' => [
                    'name' => 'Giấy chứng nhận hợp chuẩn TCVN 9844:2013',
                    'description' => "Sản phẩm vải địa kỹ thuật phù hợp tiêu chuẩn quốc gia TCVN 9844:2013 về vải địa kỹ thuật - phương pháp thử và yêu cầu kỹ thuật, đồng thời đáp ứng các tiêu chuẩn tương đương của châu Âu (EN ISO 10319).",
                ],
                'en' => [
                    'name' => 'TCVN 9844:2013 Conformity Certificate',
                    'description' => "Geotextile products conform to national standard TCVN 9844:2013 on geotextiles - test methods and technical requirements, and meet equivalent European standards (EN ISO 10319).",
                ],
            ],
        ];

        foreach ($certificates as $data) {
            $certificate = Certificate::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'image' => $data['image'],
                    'sort_order' => $data['sort_order'],
                ]
            );

            $certificate->translations()->delete();
            $certificate->translations()->createMany($this->translations([
                'vi' => $data['vi'],
                'en' => $data['en'],
            ]));
        }
    }
}

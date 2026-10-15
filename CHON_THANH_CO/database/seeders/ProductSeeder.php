<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const IMG_NONWOVEN = '/images/products/geotextile-roll.jpg';

    private const IMG_INDUSTRIAL = '/images/products/industrial-1.jpg';

    private const IMG_GABION = '/images/products/gabion-1.jpg';

    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $applications = Application::pluck('id', 'slug');

        $this->seedSeries($categories, $applications);
        $this->seedSpecialProducts($categories, $applications);
    }

    private function seedSeries($categories, $applications): void
    {
        $artStrengths = [12, 14, 17, 20, 24, 30, 40, 50, 60, 70, 90, 110, 140, 200, 280];
        $getStrengths = [5, 10, 15, 20, 30, 50, 80, 100, 150, 200, 300, 500];

        foreach ($artStrengths as $index => $strength) {
            $this->createSeriesProduct(
                $categories,
                $applications,
                category: 'vai-kt-khong-det',
                prefix: 'ART',
                slugPrefix: 'vai-kt-khong-det-art',
                strength: $strength,
                isFeatured: in_array($strength, [12, 30, 90], true),
                sortOrder: $index + 1,
                applicationSlugs: $this->artApplications($strength),
                nameVi: 'Vải địa kỹ thuật không dệt',
                nameEn: 'Non-woven Geotextile',
            );
        }

        foreach ($getStrengths as $index => $strength) {
            $this->createSeriesProduct(
                $categories,
                $applications,
                category: 'vai-kt-det',
                prefix: 'GET',
                slugPrefix: 'vai-kt-det-get',
                strength: $strength,
                isFeatured: in_array($strength, [20, 100], true),
                sortOrder: $index + 1,
                applicationSlugs: $this->getApplications($strength),
                nameVi: 'Vải địa kỹ thuật dệt',
                nameEn: 'Woven Geotextile',
            );
        }
    }

    private function createSeriesProduct(
        $categories,
        $applications,
        string $category,
        string $prefix,
        string $slugPrefix,
        int $strength,
        bool $isFeatured,
        int $sortOrder,
        array $applicationSlugs,
        string $nameVi,
        string $nameEn,
    ): void {
        $this->createProduct([
            'category_id' => $categories[$category],
            'slug' => "{$slugPrefix}-{$strength}",
            'code' => "{$prefix} {$strength}",
            'image' => self::IMG_NONWOVEN,
            'strength_min' => $strength,
            'strength_max' => $strength,
            'is_featured' => $isFeatured,
            'sort_order' => $sortOrder,
            'name' => "{$nameVi} {$strength} kN/m",
            'description' => "{$nameVi} cường độ {$strength} kN/m được sản xuất từ 100% sợi PP/PET, liên kết bằng công nghệ xuyên kim, đạt TCVN 9844:2013 và tiêu chuẩn châu Âu. Sản phẩm ứng dụng cho {$this->appNames($applicationSlugs, 'vi')}.",
            'strength_label' => "{$strength} kN/m",
            'meta_title' => "{$nameVi} {$strength} kN/m - CHON THANH",
        ], [
            [
                'icon' => 'ruler', 'value' => '1m – 6m', 'label' => 'Khổ rộng', 'sort_order' => 1,
            ],
            [
                'icon' => 'scroll-text', 'value' => '50m – 100m', 'label' => 'Chiều dài', 'sort_order' => 2,
            ],
            [
                'icon' => 'gauge', 'value' => "{$strength} kN/m", 'label' => 'Cường độ chịu kéo', 'sort_order' => 3,
            ],
            [
                'icon' => 'shield-check', 'value' => 'TCVN 9844:2013', 'label' => 'Tiêu chuẩn', 'sort_order' => 4,
            ],
        ], $applicationSlugs);
    }

    private function seedSpecialProducts($categories, $applications): void
    {
        $items = [
            [
                'slug' => 'luoi-dia-ky-thuat-geogrid',
                'code' => 'GG 30 ÷ GG 50',
                'category' => 'luoi-dia-ky-thuat',
                'image' => self::IMG_INDUSTRIAL,
                'strength' => [30, 50],
                'featured' => true,
                'apps' => ['gia-co-nen', 'on-dinh-mai-doc'],
                'name' => 'Lưới địa kỹ thuật Geogrid',
                'description' => 'Lưới địa kỹ thuật sản xuất từ polymer cường độ cao hoặc sợi thủy tinh, có cấu trúc mạng lưới đồng nhất giúp phân tán tải trọng và tăng cường sức chịu tải của nền đất. Ứng dụng gia cố nền đường, tường chắn đất và ổn định mái dốc.',
                'functions' => <<<'TEXT'
                    ## Ưu điểm nổi bật

                    - Cấu trúc mạng lưới **đồng nhất** giúp phân tán tải trọng theo nhiều phương
                    - **Giảm độ dốc mái**, tiết kiệm khối lượng đất đắp so với giải pháp truyền thống
                    - Lắp dựng nhanh bằng **cọc đóng ghim**, không cần vữa xây
                    - Chịu được tải động và biến dạng lớn trong thời gian dài

                    ## Lưu ý khi thi công

                    1. Mở phẳng lưới trước khi định vị, **càng phẳng càng tốt**
                    2. Mũi lưới giữa hai cuộn **chồng lấn tối thiểu 30 cm**
                    3. Lớp đệm đá đầm kỹ trước khi đổ đất đánh ốp
                    TEXT,
                'applications_text' => <<<'TEXT'
                    Trong thi công giao thông và thủy lợn, geogrid thường làm lớp gia cố nằm trong móng cấp phối, cho phép điều chỉnh chiều dày nền theo từng vị trí thay vì bải phẳng toàn tuyến.

                    ## Gợi ý bố trí theo vị trí

                    - Nền đường ô tô: trải ở **lớp dưới cùng** của móng cấp phối đá dăm
                    - Kè bảo vệ bờ: lưới đi kèm lớp đá hộc bọc **tường chắn**, giữ đất khỏi xói mòn
                    - Mái dốc trồng cỏ: lưới làm khung **giữ lớp đất màu** và rễ cây bám chặt

                    Nhân viên kỹ thuật của CHƠN THÀNH sẽ tư vấn vị trí đặt lưới cho công trình cụ thể.
                    TEXT,
                'specs' => [
                    ['icon' => 'ruler', 'value' => '1m – 5m', 'label' => 'Khổ rộng', 'sort_order' => 1],
                    ['icon' => 'scroll-text', 'value' => '50m', 'label' => 'Chiều dài', 'sort_order' => 2],
                    ['icon' => 'gauge', 'value' => '30 – 50 kN/m', 'label' => 'Cường độ chịu kéo', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'tham-3d-chong-xoi-mon',
                'code' => 'ECM 3D',
                'category' => 'tham-3d',
                'image' => self::IMG_GABION,
                'strength' => [null, null],
                'featured' => true,
                'apps' => ['chong-xoi-mon', 'on-dinh-mai-doc'],
                'name' => 'Thảm 3D chống xói mòn',
                'description' => 'Thảm 3D chống xói mòn cấu tạo từ các sợi polymer đan xen ba chiều tạo cấu trúc rỗng giữ đất và giữ hạt giống. Cho phép thực vật phát triển xuyên qua, hình thành hệ rễ tự nhiên gia cố bề mặt, bảo vệ mái dốc, kênh mương và bờ sông khỏi xói mòn.',
                'specs' => [
                    ['icon' => 'ruler', 'value' => '2m', 'label' => 'Khổ rộng', 'sort_order' => 1],
                    ['icon' => 'scroll-text', 'value' => '25m – 50m', 'label' => 'Chiều dài', 'sort_order' => 2],
                    ['icon' => 'layers', 'value' => '10mm – 20mm', 'label' => 'Độ dày', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'ro-da-gabion',
                'code' => 'GAB 1x1x1',
                'category' => 'ro-da',
                'image' => self::IMG_GABION,
                'strength' => [null, null],
                'featured' => true,
                'apps' => ['chong-xoi-mon', 'on-dinh-mai-doc'],
                'name' => 'Rọ đá (Gabion)',
                'description' => 'Rọ đá chế tạo từ lưới thép xoắn kép mạ kẽm nhúng nóng hoặc bọc PVC, đổ đầy đá để gia cố bờ kè, mái dốc và tường chắn. Sản phẩm có độ bền cao, khả năng thoát nước tự nhiên và thân thiện với môi trường. Sản xuất tại nhà máy Rọ đá Á Châu, Hóc Môn.',
                'functions' => <<<'TEXT'
                    ## Đặc điểm

                    - Lưới thép xoắn kép **chịu lực tốt** trong điều kiện khắc nghiệt
                    - Đá trong lồng tạo khối **cứng và thoát nước tự nhiên**, không gây áp lực nước ngầm
                    - Có thể **dỡ đổ dạng khối**, tận dụng lại quay vòng khi cần tu bổ
                    - Tự thích nghi với biến dạng nền do động đất mà không bị nứt vỡ

                    ## Chọn lớp bảo vệ

                    1. Mạ kẽm nhúng nóng — phù hợp điều kiện bình thường, giá thành thấp
                    2. Bọc PVC — dành cho công trình ven biển, đất xâm thủy, vùng nước mặn
                    TEXT,
                'applications_text' => <<<'TEXT'
                    Rọ đá thường xuất hiện ở những vị trí cấp phối đắn quá tốn kém nhưng phải giữ được bề mặt tự nhiên: trước đê, quanh móng cầu, hay trên sườn mái dốc có trồng cây.

                    ## Kinh nghiệm lắp đặt

                    - Dựng khung rọ trước, **đổ đá từng lớp** và gõ chặt từng lớp để rọ không mềm
                    - Không dùng đá có góc sắc nhọn, tránh cắt đứt dây lưới
                    - Lưới đáy và lưới vách phải **liên kết với nhau**, tuyệt đối không để hở
                    - Sau trận mưa lớn cần bổ sung đá ở những chỗ bị lún cục bộ
                    TEXT,
                'specs' => [
                    ['icon' => 'box', 'value' => '1x1x1m / 2x1x1m', 'label' => 'Kích thước', 'sort_order' => 1],
                    ['icon' => 'cable', 'value' => '2.0 – 3.0 mm', 'label' => 'Đường kính dây', 'sort_order' => 2],
                    ['icon' => 'shield-check', 'value' => 'Mạ kẽm / Bọc PVC', 'label' => 'Lớp bảo vệ', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'bang-tham-bentonite-gcl',
                'code' => 'GCL',
                'category' => 'bang-tham',
                'image' => self::IMG_NONWOVEN,
                'strength' => [null, null],
                'featured' => false,
                'apps' => ['chong-tham'],
                'name' => 'Băng thấm Bentonite (GCL)',
                'description' => 'Băng thấm bentonite GCL gồm lớp bentonite natri kẹp giữa hai lớp vải địa kỹ thuật, có hệ số thấm cực thấp. Ứng dụng chống thấm hồ chứa, ao nuôi, bãi chôn lấp, kênh mương và công trình môi trường.',
                'specs' => [
                    ['icon' => 'ruler', 'value' => '4m – 5m', 'label' => 'Khổ rộng', 'sort_order' => 1],
                    ['icon' => 'scroll-text', 'value' => '30m – 50m', 'label' => 'Chiều dài', 'sort_order' => 2],
                    ['icon' => 'droplets', 'value' => '≤ 5x10⁻¹¹ m/s', 'label' => 'Hệ số thấm', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'mang-chong-tham-hdpe',
                'code' => 'HDPE 0.5 ÷ 2.5',
                'category' => 'mang-chong-tham',
                'image' => self::IMG_INDUSTRIAL,
                'strength' => [null, null],
                'featured' => false,
                'apps' => ['chong-tham'],
                'name' => 'Màng chống thấm HDPE',
                'description' => 'Màng chống thấm HDPE sản xuất từ nhựa polyethylene mật độ cao, có khả năng chống thấm tuyệt đối, chịu hóa chất và tia UV. Ứng dụng trong hồ chứa, ao hồ, bãi chôn lấp, kênh mương và các công trình bảo vệ môi trường.',
                'functions' => <<<'TEXT'
                    ## Tính năng

                    - **Chống thấm tuyệt đối**, không hấp thụ nước
                    - Chịu hóa chất tốt với dung dịch muối, axit loãng và nước thải công nghiệp
                    - Ổn định dưới tia **UV** trong thời gian dài
                    - Có thể **hàn nhiệt** mối nối bằng máy hàn, tạo mặt phẳng liền mạch

                    ## Các kiểu màng

                    1. Màng một lớp — dùng cho nền thông thường, tiết kiệm chi phí
                    2. Màng hai lớp — dùng cho bãi chôn lấp, đáy ao hồ, nơi nguy cơ rò rỉ cao
                    TEXT,
                'applications_text' => <<<'TEXT'
                    Màng HDPE thường làm lớp chống thấm thứ hai, đặt bên dưới lớp bảo vệ geotextile để tránh cát đá gây rách màng trong lúc thi công.

                    ## Kinh nghiệm thi công

                    - **Đổ cát sanh và làm phẳng** trước khi trải màng, không để sỏi sắc nhọn
                    - Nối mối bằng cách **hàn nhiệt**, không dùng keo dán
                    - Kho màng ở chỗ râm mát, trải **không quá 20 m** một lượt trước khi hàn
                    - Phủ lớp bảo vệ xong phải **thử nghiệm rò rỉ** trước khi nghiệm thu
                    TEXT,
                'specs' => [
                    ['icon' => 'ruler', 'value' => '5.8m – 7.5m', 'label' => 'Khổ rộng', 'sort_order' => 1],
                    ['icon' => 'scroll-text', 'value' => '50m – 100m', 'label' => 'Chiều dài', 'sort_order' => 2],
                    ['icon' => 'layers', 'value' => '0.5mm – 2.5mm', 'label' => 'Độ dày', 'sort_order' => 3],
                    ['icon' => 'droplets', 'value' => '≤ 1x10⁻¹³ m/s', 'label' => 'Hệ số thấm', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'luoi-thep-b40',
                'code' => 'B40',
                'category' => 'luoi-thep-day-kem',
                'image' => self::IMG_INDUSTRIAL,
                'strength' => [null, null],
                'featured' => false,
                'apps' => ['hang-rao-bao-ve'],
                'name' => 'Lưới thép B40',
                'description' => 'Lưới thép B40 đan chéo tiêu chuẩn, mạ kẽm nhúng nóng chống gỉ, dùng làm hàng rào bảo vệ công trình, khu dân cư, trang trại và công trường.',
                'specs' => [
                    ['icon' => 'ruler', 'value' => '0.6m – 1.2m', 'label' => 'Khổ lưới', 'sort_order' => 1],
                    ['icon' => 'grid-2x2', 'value' => '50x50 / 75x150 mm', 'label' => 'Mắt lưới', 'sort_order' => 2],
                    ['icon' => 'cable', 'value' => '2.7 – 3.5 mm', 'label' => 'Đường kính', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'luoi-thep-han',
                'code' => 'WELD 50/50',
                'category' => 'luoi-thep-day-kem',
                'image' => self::IMG_INDUSTRIAL,
                'strength' => [null, null],
                'featured' => false,
                'apps' => ['hang-rao-bao-ve'],
                'name' => 'Lưới thép hàn',
                'description' => 'Lưới thép hàn chắc chắn, mắt lưới đồng đều, mạ kẽm chống ăn mòn. Dùng làm hàng rào khu công nghiệp, khu dân cư, chăn nuôi và lồng lọc.',
                'specs' => [
                    ['icon' => 'ruler', 'value' => '1m – 2m', 'label' => 'Khổ lưới', 'sort_order' => 1],
                    ['icon' => 'grid-2x2', 'value' => '50x50 / 100x100 mm', 'label' => 'Mắt lưới', 'sort_order' => 2],
                    ['icon' => 'cable', 'value' => '3.5 – 6 mm', 'label' => 'Đường kính', 'sort_order' => 3],
                ],
            ],
            [
                'slug' => 'day-kem-gai',
                'code' => 'BARB 2',
                'category' => 'luoi-thep-day-kem',
                'image' => self::IMG_INDUSTRIAL,
                'strength' => [null, null],
                'featured' => false,
                'apps' => ['hang-rao-bao-ve'],
                'name' => 'Dây kẽm gai',
                'description' => 'Dây kẽm gai 2 sợi, mạ kẽm nhúng nóng, gai sắc nhọn đều. Dùng làm hàng rào an ninh cho công trình, nhà máy, trang trại và khu quân sự.',
                'specs' => [
                    ['icon' => 'scroll-text', 'value' => '100m – 200m', 'label' => 'Chiều dài cuộn', 'sort_order' => 1],
                    ['icon' => 'layers', 'value' => '2 sợi', 'label' => 'Số sợi', 'sort_order' => 2],
                    ['icon' => 'cable', 'value' => '1.6 – 2.2 mm', 'label' => 'Đường kính', 'sort_order' => 3],
                ],
            ],
        ];

        $sort = 40;
        foreach ($items as $data) {
            $specs = $data['specs'];

            $attributes = [
                'category_id' => $categories[$data['category']],
                'slug' => $data['slug'],
                'code' => $data['code'],
                'image' => $data['image'],
                'strength_min' => $data['strength'][0],
                'strength_max' => $data['strength'][1],
                'is_featured' => $data['featured'],
                'sort_order' => $sort++,
                'name' => $data['name'],
                'description' => $data['description'],
            ];

            // Hai nội dung tab dưới là tùy chọn: chỉ ghi khi sản phẩm có,
            // không ghi null để không xoá nội dung admin đã nhập tay.
            foreach (['functions', 'applications_text'] as $optional) {
                if (array_key_exists($optional, $data)) {
                    $attributes[$optional] = $data[$optional];
                }
            }

            $this->createProduct($attributes, $specs, $data['apps']);
        }
    }

    private function createProduct(array $attributes, array $specs, array $applicationSlugs): void
    {
        $product = Product::updateOrCreate(
            ['code' => $attributes['code']],
            $attributes
        );

        $product->applications()->sync(
            Application::whereIn('slug', $applicationSlugs)->pluck('id')
        );

        $product->specs()->delete();
        foreach ($specs as $index => $spec) {
            ProductSpec::create([
                'product_id' => $product->id,
                'icon' => $spec['icon'],
                'value' => $spec['value'],
                'label' => $spec['label'],
                'sort_order' => $spec['sort_order'] ?? ($index + 1),
            ]);
        }
    }

    private function artApplications(int $strength): array
    {
        return match (true) {
            $strength <= 20 => ['phan-cach-loc', 'thoat-nuoc'],
            $strength <= 30 => ['phan-cach-loc', 'thoat-nuoc', 'gia-co-nen'],
            $strength <= 50 => ['gia-co-nen', 'thoat-nuoc', 'on-dinh-mai-doc'],
            $strength <= 90 => ['gia-co-nen', 'on-dinh-mai-doc', 'chong-xoi-mon'],
            $strength <= 140 => ['gia-co-nen', 'on-dinh-mai-doc'],
            default => ['gia-co-nen'],
        };
    }

    private function getApplications(int $strength): array
    {
        return match (true) {
            $strength <= 15 => ['gia-co-nen'],
            $strength <= 30 => ['gia-co-nen', 'on-dinh-mai-doc'],
            $strength <= 80 => ['gia-co-nen', 'on-dinh-mai-doc'],
            $strength <= 150 => ['gia-co-nen', 'on-dinh-mai-doc', 'chong-xoi-mon'],
            default => ['gia-co-nen'],
        };
    }

    private function appNames(array $slugs, string $locale): string
    {
        $names = Application::whereIn('slug', $slugs)->get()
            ->map(fn (Application $application) => $application->name)
            ->filter()
            ->implode(', ');

        return $names ?: implode(', ', $slugs);
    }
}

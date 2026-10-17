<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Gom các mã cùng dòng dưới một sản phẩm cha (dòng sản phẩm).
 *
 * Dựa trên catalog THẬT trong database (mã SP01–SP27), không dựa trên
 * ProductSeeder. Năm nhóm có nhiều biến thể là các sản phẩm riêng đã tồn tại:
 *
 *   - Màng chống thấm HDPE : 7 mã (HSE + 6 độ dày)
 *   - Rọ đá               : 5 mã (PVC P10/P8, P10/P8, đan tay)
 *   - Lưới thép B40        : 3 mã (ô 40mm, ô 70mm, lõi thép bọc PVC)
 *   - Lưới địa kỹ thuật   : 2 mã (gia cường cao, cốt thủy tinh)
 *   - Dây kẽm gai         : 2 mã (mạ kẽm, đao lam)
 *
 * Sản phẩm cha là một sản phẩm thật, có URL riêng, liệt kê các biến thể.
 * Idempotent: chạy lại nhiều lần không nhân bản.
 */
class ProductVariantSeeder extends Seeder
{
    /**
     * slug cha => [thông tin cha, các slug con].
     * Mỗi danh sách con được khớp theo slug chính xác, không suy đoán.
     */
    private const GROUPS = [
        'mang-chong-tham-hdpe' => [
            'parent' => [
                'category' => 'mang-chong-tham',
                'code' => 'HDPE',
                'name' => 'Màng chống thấm HDPE',
                'description' => 'Màng chống thấm HDPE (High Density Polyethylene) được sản xuất từ hạt nhựa polyethylene nguyên sinh tỷ trọng cao kết hợp hạt carbon đen, có độ bền trên 25 năm và không gây độc hại cho môi trường. Dòng sản phẩm gồm nhiều độ dày từ 0.3 mm đến 2.0 mm và loại HSE, dùng cho chống thấm hồ chứa, bãi chôn lấp, kênh mương và khu vực công nghiệp.',
            ],
            'children' => [
                'mang-chong-tham-hdpe-hse',
                'mang-chong-tham-hdpe-0-3mm',
                'mang-chong-tham-hdpe-0-5mm',
                'mang-chong-tham-hdpe-0-75mm',
                'mang-chong-tham-hdpe-1-0mm',
                'mang-chong-tham-hdpe-1-5mm',
                'mang-chong-tham-hdpe-2-0mm',
            ],
        ],
        'ro-da-gabion' => [
            'parent' => [
                'category' => 'ro-da',
                'code' => 'GABION',
                'name' => 'Rọ đá',
                'description' => 'Rọ đá (gabion) đạt tiêu chuẩn, kết cấu lưới đan kép xoắn chữ Z, mạ kẽm nhúng nóng có độ bền cao hoặc bọc nhựa PVC chống ăn mòn trong điều kiện khí hậu khắc nghiệt. Dòng sản phẩm gồm nhiều kích thước mắt lưới P8, P10 và rọ đan tay, kích thước thông dụng 2x1x0.5 m, 2x1x1 m hoặc theo bản vẽ thiết kế.',
            ],
            'children' => [
                'ro-da-ma-kem-nhung-nong-boc-nhua-pvc-p10',
                'ro-da-ma-kem-nhung-nong-boc-nhua-pvc-p8',
                'ro-da-ma-kem-nhung-nong-p10',
                'ro-da-ma-kem-nhung-nong-p8',
                'ro-da-ma-kem-dan-tay',
            ],
        ],
        'luoi-thep-b40' => [
            'parent' => [
                'category' => 'luoi-thep',
                'code' => 'B40',
                'name' => 'Lưới thép B40',
                'description' => 'Lưới thép B40 đan chéo dạng ô vuông dùng làm hàng rào bảo vệ cho công trình dân dụng và công nghiệp. Sản xuất từ thép chất lượng cao, mạ kẽm chống ăn mòn, có nhiều kích thước ô và khổ lưới khác nhau, kèm phiên bản lõi thép bọc PVC cho môi trường ăn mòn mạnh.',
            ],
            'children' => [
                'luoi-thep-b40-ma-kem-o-40mm',
                'luoi-thep-b40-ma-kem-o-70mm',
                'luoi-b40-loi-thep-boc-pvc',
            ],
        ],
        'luoi-dia-ky-thuat' => [
            'parent' => [
                'category' => 'luoi-dia-ky-thuat',
                'code' => 'GEO',
                'name' => 'Lưới địa kỹ thuật',
                'description' => 'Lưới địa kỹ thuật là vật liệu tổng hợp dùng trong xây dựng hạ tầng, có chức năng gia cố nền đất, tăng cường ổn định công trình, kiểm soát xói mòn và thoát nước. Dòng sản phẩm gồm lưới địa kỹ thuật gia cường cao và lưới địa kỹ thuật cốt thủy tinh, tùy theo yêu cầu kỹ thuật và điều kiện công trình.',
            ],
            'children' => [
                'luoi-dia-ky-thuat-gia-cuong-cao',
                'luoi-dia-ky-thuat-cot-thuy-tinh',
            ],
        ],
        'day-kem-gai' => [
            'parent' => [
                'category' => 'day-kem',
                'code' => 'BARB',
                'name' => 'Dây kẽm gai',
                'description' => 'Dây kẽm gai là giải pháp hàng rào bảo vệ hiệu quả cho công trình dân dụng và công nghiệp, sản xuất từ thép chất lượng cao có đường kính 2.5 mm, 2.7 mm và 3.0 mm, chống oxy hóa và chống gỉ trong thời gian dài. Dòng sản phẩm gồm dây kẽm gai mạ kẽm và dây kẽm gai đao lam (kẽm gai bung).',
            ],
            'children' => [
                'day-kem-gai-ma-kem',
                'day-kem-gai-dao-lam-kem-gai-bung',
            ],
        ],
    ];

    public function run(): void
    {
        $categoryIds = Category::pluck('id', 'slug');
        $missing = [];

        foreach (self::GROUPS as $parentSlug => $group) {
            $parent = $this->ensureParent($parentSlug, $group['parent'], $categoryIds);

            $children = Product::query()
                ->whereIn('slug', $group['children'])
                ->whereNull('deleted_at')
                ->get();

            foreach ($group['children'] as $childSlug) {
                if (! $children->contains('slug', $childSlug)) {
                    $missing[] = $childSlug;
                }
            }

            foreach ($children as $child) {
                if ((int) $child->parent_id !== (int) $parent->id) {
                    $child->forceFill(['parent_id' => $parent->id])->save();
                }
            }

            $this->command?->info(sprintf(
                '  %s (#%d) ← %d biến thể',
                $parent->name,
                $parent->id,
                $children->count(),
            ));
        }

        if ($missing !== []) {
            $this->command?->warn('  Không tìm thấy mã: '.implode(', ', $missing));
        }
    }

    private function ensureParent(string $slug, array $line, $categoryIds): Product
    {
        // Dùng ảnh của biến thể đầu tiên làm ảnh đại diện cho cả dòng.
        $image = Product::query()
            ->whereIn('slug', self::GROUPS[$slug]['children'])
            ->whereNull('deleted_at')
            ->whereNotNull('image')
            ->value('image');

        $attributes = [
            'category_id' => $categoryIds[$line['category']] ?? null,
            'code' => $line['code'],
            'name' => $line['name'],
            'description' => $line['description'],
            'image' => $image ?: '/images/products/industrial-1.jpg',
            'applications_text' => null,
            'meta_title' => $line['name'].' - CHON THANH',
            'meta_description' => mb_substr($line['description'], 0, 290),
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
            'parent_id' => null,
        ];

        $parent = Product::withTrashed()->where('slug', $slug)->first();

        if ($parent) {
            // Dòng tồn tại nhưng có thể đang bị soft-delete từ lần seed dữ liệu
            // mẫu trước đó — phải xoá deleted_at, nếu không save() sẽ giữ nguyên.
            $parent->forceFill($attributes);
            $parent->deleted_at = null;
            $parent->save();

            return $parent;
        }

        return Product::create($attributes + ['slug' => $slug]);
    }
}
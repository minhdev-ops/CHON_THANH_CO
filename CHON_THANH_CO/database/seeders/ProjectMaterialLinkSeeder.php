<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProjectMaterial;
use Illuminate\Database\Seeder;

/**
 * Gắn product_id cho vật liệu dự án.
 *
 * ProjectSeeder tạo vật liệu chỉ với tên văn bản, nên cột product_id
 * (FK có index nhưng nullable) luôn NULL ⇒ trang chi tiết sản phẩm không
 * dẫn được tới công trình nào.
 *
 * Vật liệu dạng "vải địa kỹ thuật" trỏ về SẢN PHẨM CHA của dòng, không trỏ
 * về một mã cụ thể: khách xem một mã vẫn thấy công trình dùng mã khác.
 *
 * CHỈ gắn 8 dòng khớp chắc chắn. "Thảm 3D chống xói mòn" cố tình bỏ qua:
 * catalog thật chỉ có "Thảm đá", đây là hai sản phẩm khác nhau, cần người
 * dùng xác nhận trước khi liên kết.
 *
 * Idempotent: chạy lại không nhân bản.
 */
class ProjectMaterialLinkSeeder extends Seeder
{
    /** Tên vật liệu trong ProjectSeeder => slug sản phẩm được liên kết. */
    private const LINKS = [
        'Lưới địa kỹ thuật' => 'luoi-dia-ky-thuat',
        'Vải địa kỹ thuật không dệt' => 'vai-dia-ky-thuat-khong-det-art',
        'Màng chống thấm HDPE' => 'mang-chong-tham-hdpe',
    ];

    public function run(): void
    {
        $productIds = Product::pluck('id', 'slug');
        $linked = 0;
        $unmatched = [];

        foreach (self::LINKS as $materialName => $productSlug) {
            $productId = $productIds[$productSlug] ?? null;

            if (! $productId) {
                $unmatched[] = $productSlug;

                continue;
            }

            $affected = ProjectMaterial::query()
                ->where('name', $materialName)
                ->whereNull('product_id')
                ->update(['product_id' => $productId]);

            $linked += $affected;
        }

        // Đếm lại toàn bộ vật liệu đang trỏ đúng, không phải số dòng vừa sửa:
        // chạy lại seeder sẽ là 0 dòng mới nhưng trạng thái vẫn đúng.
        $total = ProjectMaterial::query()
            ->whereIn('product_id', $productIds->filter()->values())
            ->count();

        $this->command?->info("  Đang liên kết {$total} vật liệu dự án với sản phẩm (cập nhật {$linked} dòng).");

        if ($unmatched !== []) {
            $this->command?->warn('  Không tìm thấy sản phẩm cho slug: '.implode(', ', $unmatched));
        }
    }
}

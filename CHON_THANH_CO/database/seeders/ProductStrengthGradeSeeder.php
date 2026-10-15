<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Nhập cấp cường độ cho các dòng có nhiều cấp trong cùng một sản phẩm.
 *
 * Trước đây các cấp nằm chung trong description ("7kN/m; 9kN/m; ...") nên
 * khách không lọc hay so sánh được. Đưa vào bảng riêng để admin sửa từng cấp
 * và trang chi tiết hiện thành bảng chọn.
 *
 * Dữ liệu lấy nguyên văn từ description hiện có, đã được duyệt.
 *
 * Idempotent: xóa rồi tạo lại theo danh sách.
 */
class ProductStrengthGradeSeeder extends Seeder
{
    /** slug sản phẩm => danh sách [cấp, đơn vị, ghi chú]. */
    private const GRADES = [
        'vai-dia-ky-thuat-khong-det-art' => [
            ['7', 'kN/m'],
            ['9', 'kN/m'],
            ['12', 'kN/m'],
            ['14', 'kN/m'],
            ['15', 'kN/m'],
            ['25', 'kN/m'],
        ],
        'vai-dia-ky-thuat-det-get' => [
            ['20 - 200', 'g/m²'],
            ['TS 40 - 50', 'kN/m'],
        ],
    ];

    public function run(): void
    {
        foreach (self::GRADES as $slug => $rows) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                $this->command?->warn("  Bỏ qua: không tìm thấy sản phẩm {$slug}.");

                continue;
            }

            $product->strengthGrades()->delete();

            foreach ($rows as $index => $row) {
                $product->strengthGrades()->create([
                    'grade' => $row[0],
                    'unit' => $row[1] ?? null,
                    'note' => $row[2] ?? null,
                    'sort_order' => $index,
                ]);
            }

            $this->command?->info(sprintf(
                '  %s: %d cấp cường độ.',
                $product->code,
                count($rows)
            ));
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Gắn ứng dụng cho sản phẩm.
 *
 * Bảng product_application trống hoàn toàn nên bộ lọc theo ứng dụng và
 * phần "Ứng dụng" trên trang chi tiết không có gì để hiển thị. Án xạ dựa
 * trên bản chất vật liệu, đã được người dùng duyệt trước khi ghi vào DB.
 *
 * Idempotent: sync theo danh sách, chạy lại cho kết quả như nhau.
 */
class ProductApplicationSeeder extends Seeder
{
    /** slug sản phẩm (hoặc tiền tố dòng) => slug ứng dụng. */
    private const MAP = [
        // Vải địa kỹ thuật: dùng cho cả lọc, thoát nước, gia cố và chống xói mòn.
        'vai-dia-ky-thuat-khong-det-art' => [
            'phan-cach-loc', 'thoat-nuoc', 'gia-co-nen', 'chong-xoi-mon', 'on-dinh-mai-doc',
        ],
        'vai-dia-ky-thuat-det-get' => [
            'phan-cach-loc', 'thoat-nuoc', 'gia-co-nen', 'chong-xoi-mon', 'on-dinh-mai-doc',
        ],

        // Lưới địa kỹ thuật: gia cố nền, giữ ổn định mái dốc, chống xói mòn.
        'luoi-dia-ky-thuat' => ['gia-co-nen', 'chong-xoi-mon', 'on-dinh-mai-doc'],

        // Rọ đá: kè chắn, ổn định mái dốc, chống xói mòn bờ.
        'ro-da-gabion' => ['on-dinh-mai-doc', 'chong-xoi-mon'],

        // Màng chống thấm.
        'mang-chong-tham-hdpe' => ['chong-tham'],
        'mang-chong-tham-bentonite-gcl' => ['chong-tham'],
        'giay-dau' => ['chong-tham'],

        // Bảo vệ bề mặt và mái dốc.
        'tham-da' => ['chong-xoi-mon', 'on-dinh-mai-doc'],
        'be-tong-vai-cuon' => ['chong-xoi-mon', 'on-dinh-mai-doc'],

        // Hàng rào và bảo vệ.
        'day-kem-gai' => ['hang-rao-bao-ve'],
        'luoi-thep-b40' => ['hang-rao-bao-ve'],
        'luoi-thep-han' => ['hang-rao-bao-ve'],
        'luoi-mat-cao-to-tuong' => ['hang-rao-bao-ve'],
    ];

    public function run(): void
    {
        $applicationIds = Application::pluck('id', 'slug');
        $missingApps = [];

        // Gom theo dòng: mọi biến thể của dòng dùng chung ứng dụng với cha.
        $resolved = [];

        foreach (self::MAP as $lineSlug => $appSlugs) {
            $ids = collect($appSlugs)
                ->map(fn ($slug) => $applicationIds[$slug] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach ($appSlugs as $slug) {
                if (! isset($applicationIds[$slug])) {
                    $missingApps[$slug] = true;
                }
            }

            $products = Product::query()
                ->whereNull('deleted_at')
                ->where(function ($q) use ($lineSlug) {
                    $q->where('slug', $lineSlug)
                        ->orWhereHas('parent', fn ($p) => $p->where('slug', $lineSlug));
                })
                ->get();

            foreach ($products as $product) {
                $resolved[$product->id] = $ids;
            }
        }

        $linked = 0;
        foreach ($resolved as $productId => $appIds) {
            $linked += Product::find($productId)?->applications()->sync($appIds) ? count($appIds) : 0;
        }

        $this->command?->info(sprintf(
            '  Gắn %d liên kết ứng dụng cho %d sản phẩm.',
            $linked,
            count($resolved),
        ));

        if ($missingApps !== []) {
            $this->command?->warn(
                '  Không tìm thấy ứng dụng: '.implode(', ', array_keys($missingApps))
            );
        }
    }
}

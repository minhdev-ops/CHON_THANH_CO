<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Project;
use Illuminate\Support\Collection;

class ProductService
{
    public function list(array $filters = [], string $locale = 'vi', int $limit = 12): array
    {
        $query = Product::query()
            ->with(['category', 'applications'])
            ->where('is_active', true);

        if (! empty($filters['category'])) {
            $slugs = $this->splitSlugs($filters['category']);
            if ($slugs) {
                $query->whereHas('category', fn ($q) => $q->whereIn('slug', $slugs));
            }
        }

        if (! empty($filters['application'])) {
            $slugs = $this->splitSlugs($filters['application']);
            if ($slugs) {
                $query->whereHas('applications', fn ($q) => $q->whereIn('slug', $slugs));
            }
        }

        if (isset($filters['strength_min']) && $filters['strength_min'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('strength_max', '>=', $filters['strength_min'])
                    ->orWhereNull('strength_max');
            });
        }

        if (isset($filters['strength_max']) && $filters['strength_max'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('strength_min', '<=', $filters['strength_max'])
                    ->orWhereNull('strength_min');
            });
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['cursor'])) {
            $query->where('id', '<', (int) $filters['cursor']);
        }

        $items = $query->orderByDesc('id')->limit($limit)->get();

        return [
            'items' => $items,
            'next_cursor' => $items->count() === $limit ? $items->last()->id : null,
        ];
    }

    protected function splitSlugs(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    public function findBySlug(string $slug, string $locale = 'vi'): ?Product
    {
        return Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category',
                'applications',
                'specs',
                'images',
                'documents',
                'parent',
                'strengthGrades',
                'variants',
            ])
            ->first();
    }

    /**
     * Sản phẩm liên quan, xếp hạng theo mức độ liên quan.
     *
     * Thứ tự ưu tiên: cùng dòng sản phẩm → có ứng dụng chung → cùng danh mục.
     * Trả về tối đa $limit sản phẩm, luôn loại sản phẩm đang xem.
     */
    public function related(Product $product, int $limit = 8): Collection
    {
        $lineId = $product->lineId();
        $currentApplications = $product->applications->pluck('id')->all();
        $currentCategoryId = $product->category_id;

        // Gom ứng viên: cùng dòng, cùng danh mục, hoặc có chung ứng dụng.
        // Toàn bộ điều kiện OR phải nằm trong cùng một closure. Nếu để
        // orWhere ngoài closure, SQL sẽ thành "A AND B OR C" khiến điều
        // kiện loại trừ bị bỏ qua.
        $candidates = Product::query()
            ->with(['category', 'applications'])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($lineId, $currentCategoryId, $currentApplications) {
                $q->where('parent_id', $lineId)
                    ->orWhere('id', $lineId);

                if ($currentCategoryId) {
                    $q->orWhere('category_id', $currentCategoryId);
                }

                if ($currentApplications !== []) {
                    $q->orWhereHas(
                        'applications',
                        fn ($sub) => $sub->whereIn('applications.id', $currentApplications)
                    );
                }
            })
            ->get();

        return $candidates
            ->map(function (Product $candidate) use ($lineId, $currentApplications, $product) {
                $sharedApplications = $candidate->applications
                    ->pluck('id')
                    ->intersect($currentApplications)
                    ->count();

                $score = 0;
                // Cùng dòng sản phẩm: mức độ liên quan cao nhất.
                if ((int) $candidate->lineId() === (int) $lineId) {
                    $score += 100;
                }
                $score += $sharedApplications * 10;
                if ($candidate->category_id && $candidate->category_id === $product->category_id) {
                    $score += 5;
                }
                if ($candidate->is_featured) {
                    $score += 2;
                }

                return ['product' => $candidate, 'score' => $score];
            })
            ->sortBy([
                // Điểm giảm dần, rồi sort_order tăng dần, rồi id tăng dần để
                // thứ tự ổn định giữa các request.
                fn ($a, $b) => $b['score'] <=> $a['score'],
                fn ($a, $b) => ($a['product']->sort_order ?? 0) <=> ($b['product']->sort_order ?? 0),
                fn ($a, $b) => $a['product']->id <=> $b['product']->id,
            ])
            ->take($limit)
            ->pluck('product')
            ->values();
    }

    /**
     * Công trình đã dùng sản phẩm thuộc dòng của $product.
     *
     * Khách xem một mã cụ thể (ART 12) vẫn thấy công trình dùng mã khác
     * cùng dòng, vì dự án thường dùng nhiều mã của một dòng.
     */
    public function projectsOfLine(Product $product): Collection
    {
        $variantIds = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($product) {
                $q->where('parent_id', $product->lineId())->orWhere('id', $product->lineId());
            })
            ->pluck('id')
            ->push($product->id)
            ->unique()
            ->all();

        if ($variantIds === []) {
            return collect();
        }

        return Project::query()
            ->where('is_active', true)
            ->whereHas('materials', fn ($q) => $q->whereIn('product_id', $variantIds))
            ->with(['materials', 'images'])
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function featured(string $locale = 'vi', int $limit = 8): Collection
    {
        return Product::query()
            ->with(['category'])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
}
<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductService
{
    public function list(array $filters = [], string $locale = 'vi', int $limit = 12): array
    {
        $query = Product::query()
            ->with(['translations', 'category.translations', 'applications.translations'])
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
            $query->whereHas('translations', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%');
            });
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
            ->with(['translations', 'category.translations', 'applications.translations', 'specs.translations', 'images'])
            ->first();
    }

    public function featured(string $locale = 'vi', int $limit = 8): Collection
    {
        return Product::query()
            ->with(['translations', 'category.translations'])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
}

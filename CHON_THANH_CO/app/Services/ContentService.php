<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Faq;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Support\Collection;

class ContentService
{
    public function certificates(string $locale = 'vi'): Collection
    {
        return Certificate::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function news(array $filters = [], string $locale = 'vi', int $limit = 12): array
    {
        $query = News::query()
            ->with(['translations', 'category.translations'])
            ->where('is_active', true);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
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

    public function newsCategories(string $locale = 'vi'): Collection
    {
        return NewsCategory::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function newsDetail(string $slug, string $locale = 'vi'): ?News
    {
        return News::query()
            ->with(['translations', 'category.translations'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function faqs(string $locale = 'vi'): Collection
    {
        return Faq::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}

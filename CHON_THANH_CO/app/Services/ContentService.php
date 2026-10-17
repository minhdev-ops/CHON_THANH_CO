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
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function news(array $filters = [], string $locale = 'vi', int $limit = 12): array
    {
        $query = News::query()
            ->with(['category'])
            ->where('is_active', true);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        // Bài mới nhất lên đầu theo ngày đăng mà admin chọn, không theo id.
        // Id chỉ đóng vai trò phá thế hoàn bằng cho các bài trùng ngày.
        $query->orderByDesc('published_at')->orderByDesc('id');

        // Cursor gộp "ngày đăng|id" để khớp với thứ tự sắp xếp. Cursor chỉ
        // theo id sẽ bỏ sót bài khi ngày đăng không đồng đều với id.
        if (! empty($filters['cursor'])) {
            [$cursorDate, $cursorId] = array_pad(explode('|', (string) $filters['cursor'], 2), 2, null);

            if ($cursorDate !== null && $cursorId !== null) {
                $query->where(function ($q) use ($cursorDate, $cursorId) {
                    $q->where('published_at', '<', $cursorDate)
                        ->orWhere(function ($sub) use ($cursorDate, $cursorId) {
                            $sub->where('published_at', $cursorDate)
                                ->where('id', '<', (int) $cursorId);
                        });
                });
            }
        }

        $items = $query->limit($limit)->get();

        return [
            'items' => $items,
            'next_cursor' => $items->count() === $limit ? $this->newsCursor($items->last()) : null,
        ];
    }

    /** Cursor cho trang tin: ghép ngày đăng và id để phân trang không trùng lặp. */
    private function newsCursor(?News $item): ?string
    {
        if (! $item) {
            return null;
        }

        return $item->published_at?->format('Y-m-d H:i:s').'|'.$item->id;
    }

    public function newsCategories(string $locale = 'vi'): Collection
    {
        return NewsCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function newsDetail(string $slug, string $locale = 'vi'): ?News
    {
        return News::query()
            ->with(['category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function faqs(string $locale = 'vi'): Collection
    {
        return Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
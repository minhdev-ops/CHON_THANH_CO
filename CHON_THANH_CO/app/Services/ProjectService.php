<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Collection;

class ProjectService
{
    public function list(array $filters = [], string $locale = 'vi', int $limit = 12): array
    {
        $query = Project::query()
            ->with(['translations'])
            ->where('is_active', true);

        if (! empty($filters['cursor'])) {
            $query->where('id', '<', (int) $filters['cursor']);
        }

        $items = $query->orderByDesc('id')->limit($limit)->get();

        return [
            'items' => $items,
            'next_cursor' => $items->count() === $limit ? $items->last()->id : null,
        ];
    }

    public function findBySlug(string $slug, string $locale = 'vi'): ?Project
    {
        return Project::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['translations', 'materials.translations', 'materials.product', 'images'])
            ->first();
    }

    public function featured(string $locale = 'vi', int $limit = 6): Collection
    {
        return Project::query()
            ->with(['translations'])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
}

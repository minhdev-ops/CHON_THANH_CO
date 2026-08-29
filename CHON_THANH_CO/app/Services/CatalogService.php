<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Category;
use Illuminate\Support\Collection;

class CatalogService
{
    public function categories(string $locale = 'vi'): Collection
    {
        return Category::query()
            ->with(['translations'])
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function applications(string $locale = 'vi'): Collection
    {
        return Application::query()
            ->with(['translations'])
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}

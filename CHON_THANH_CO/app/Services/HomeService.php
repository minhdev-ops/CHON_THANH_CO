<?php

namespace App\Services;

use App\Models\AboutTimeline;
use App\Models\Banner;
use App\Models\HomeStat;
use App\Models\Setting;
use App\Models\WhyChooseUs;
use Illuminate\Support\Collection;

class HomeService
{
    public function stats(string $locale = 'vi'): Collection
    {
        return HomeStat::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function whyChooseUs(string $locale = 'vi'): Collection
    {
        return WhyChooseUs::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function banners(string $locale = 'vi'): Collection
    {
        return Banner::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();
    }

    public function timeline(string $locale = 'vi'): Collection
    {
        return AboutTimeline::query()
            ->with(['translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function settings(): Collection
    {
        return Setting::query()->orderBy('group')->get();
    }
}

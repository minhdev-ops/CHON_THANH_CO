<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:generate', function () {
    $this->info('Generating sitemap...');

    $sitemap = \Spatie\Sitemap\Sitemap::create();

    $baseUrl = config('app.url');

    // Home page
    $sitemap->add(\Spatie\Sitemap\Tags\Url::create($baseUrl)->setLastModificationDate(now())->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0));

    // Static pages
    foreach (['/about', '/products', '/projects', '/certificates', '/contact', '/news', '/faq'] as $path) {
        $sitemap->add(\Spatie\Sitemap\Tags\Url::create($baseUrl . $path)
            ->setLastModificationDate(now())
            ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8));
    }

    // Categories
    \App\Models\Category::where('is_active', true)->get()->each(function ($category) use ($sitemap, $baseUrl) {
        $sitemap->add(\Spatie\Sitemap\Tags\Url::create($baseUrl . '/products?category=' . $category->slug)
            ->setLastModificationDate($category->updated_at)
            ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.7));
    });

    // Products
    \App\Models\Product::where('is_active', true)->chunkById(100, function ($products) use ($sitemap, $baseUrl) {
        foreach ($products as $product) {
            $sitemap->add(\Spatie\Sitemap\Tags\Url::create($baseUrl . '/products/' . $product->slug)
                ->setLastModificationDate($product->updated_at)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.6));
        }
    });

    // Projects
    \App\Models\Project::where('is_active', true)->chunkById(100, function ($projects) use ($sitemap, $baseUrl) {
        foreach ($projects as $project) {
            $sitemap->add(\Spatie\Sitemap\Tags\Url::create($baseUrl . '/projects/' . $project->slug)
                ->setLastModificationDate($project->updated_at)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6));
        }
    });

    // News
    \App\Models\News::where('is_active', true)->chunkById(100, function ($news) use ($sitemap, $baseUrl) {
        foreach ($news as $item) {
            $sitemap->add(\Spatie\Sitemap\Tags\Url::create($baseUrl . '/news/' . $item->slug)
                ->setLastModificationDate($item->updated_at)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5));
        }
    });

    $sitemap->writeToFile(public_path('sitemap.xml'));

    $this->info('Sitemap generated successfully at ' . public_path('sitemap.xml'));
})->purpose('Generate sitemap.xml for the website');

Schedule::command('sitemap:generate')->daily();

// Run backup every day at 1:00 AM
Schedule::command('backup:clean')->dailyAt('01:00');
Schedule::command('backup:run')->dailyAt('01:30');
Schedule::command('news:import')->dailyAt('03:30');
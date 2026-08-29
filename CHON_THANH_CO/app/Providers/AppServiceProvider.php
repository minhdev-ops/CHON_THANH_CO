<?php

namespace App\Providers;

use App\Models\AboutTimeline;
use App\Models\Application;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\HomeStat;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Models\WhyChooseUs;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected const OBSERVED_MODELS = [
        AboutTimeline::class,
        Category::class,
        Application::class,
        Product::class,
        Project::class,
        Certificate::class,
        News::class,
        NewsCategory::class,
        Faq::class,
        HomeStat::class,
        WhyChooseUs::class,
        Banner::class,
        Setting::class,
        ContactMessage::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (self::OBSERVED_MODELS as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}

<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use Laravel\Scout\ScoutServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Sitemap\SitemapServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    ScoutServiceProvider::class,
    MediaLibraryServiceProvider::class,
    SitemapServiceProvider::class,
];
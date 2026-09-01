<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('set.locale')->prefix('v1')->group(function () {
    Route::get('home', [HomeController::class, 'index']);
    Route::get('settings', [HomeController::class, 'settings']);
    Route::get('about/timeline', [HomeController::class, 'timeline']);

    Route::get('categories', [CatalogController::class, 'categories']);
    Route::get('applications', [CatalogController::class, 'applications']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{slug}', [ProjectController::class, 'show']);

    Route::get('certificates', [CertificateController::class, 'index']);

    Route::get('profile-book', [\App\Http\Controllers\Api\ProfileBookController::class, 'show']);
    Route::post('profile-book/upload', [\App\Http\Controllers\Api\ProfileBookController::class, 'upload']);

    Route::get('news/categories', [NewsController::class, 'categories']);
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{slug}', [NewsController::class, 'show']);

    Route::get('faqs', [FaqController::class, 'index']);

    Route::post('contact', [ContactController::class, 'store']);
});

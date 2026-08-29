<?php

use App\Http\Controllers\Admin\AboutTimelineController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\HomeStatController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WhyChooseUsController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('admin.path', 'admin'))->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('admin.auth')->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('applications', ApplicationController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::get('products/template', [ProductController::class, 'template'])->name('products.template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::post('projects/bulk', [ProjectController::class, 'bulk'])->name('projects.bulk');
        Route::resource('certificates', CertificateController::class)->except(['show']);
        Route::resource('news', NewsController::class)->except(['show'])
            ->parameters(['news' => 'item']);
        Route::post('news/bulk', [NewsController::class, 'bulk'])->name('news.bulk');
        Route::resource('news-categories', NewsCategoryController::class)->except(['show']);
        Route::resource('faqs', FaqController::class)->except(['show']);
        Route::resource('home-stats', HomeStatController::class)->except(['show'])
            ->parameters(['home-stats' => 'stat']);
        Route::resource('why-choose-us', WhyChooseUsController::class)->except(['show'])
            ->parameters(['why-choose-us' => 'item']);
        Route::resource('banners', BannerController::class)->except(['show']);
        Route::resource('about-timeline', AboutTimelineController::class)->except(['show'])
            ->parameters(['about-timeline' => 'item']);

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::prefix('files')->name('files.')->group(function () {
            Route::get('/', [FileManagerController::class, 'index'])->name('index');
            Route::get('picker', [FileManagerController::class, 'picker'])->name('picker');
            Route::get('browse', [FileManagerController::class, 'browse'])->name('browse');
            Route::post('upload', [FileManagerController::class, 'upload'])->name('upload');
            Route::post('create-folder', [FileManagerController::class, 'createFolder'])->name('create-folder');
            Route::post('rename', [FileManagerController::class, 'rename'])->name('rename');
            Route::delete('delete', [FileManagerController::class, 'destroy'])->name('destroy');
        });

        Route::get('contacts', [ContactMessageController::class, 'index'])->name('contacts.index');
        Route::get('contacts/{contact}', [ContactMessageController::class, 'show'])->name('contacts.show');
        Route::post('contacts/{contact}/read', [ContactMessageController::class, 'markRead'])->name('contacts.read');
        Route::post('contacts/{contact}/reply', [ContactMessageController::class, 'reply'])->name('contacts.reply');
        Route::delete('contacts/{contact}', [ContactMessageController::class, 'destroy'])->name('contacts.destroy');
    });
});

// Khi ADMIN_PATH đã đổi khỏi 'admin', chặn đường dẫn cũ trả 404 để không lộ thông tin
if (config('admin.path') !== 'admin') {
    Route::prefix('admin')->group(function () {
        Route::any('/{any}', fn () => abort(404))->where('any', '.*');
        Route::any('/', fn () => abort(404));
    });
}

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

<?php

use App\Models\Application;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\HomeStat;
use App\Models\NewsCategory;
use App\Models\WhyChooseUs;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('can update an application', function () {
    $app = Application::create(['slug' => 'ung-dung-goc', 'sort_order' => 1, 'is_active' => true]);
    $app->translations()->create(['locale' => 'vi', 'name' => 'Ứng dụng ban đầu', 'description' => 'Mô tả cũ']);

    $this->put("/admin/applications/{$app->id}", [
        'sort_order' => 2,
        'is_active' => 0,
        'translations' => [
            'vi' => ['name' => 'Ứng dụng đã sửa', 'description' => 'Mô tả mới'],
            'en' => ['name' => 'Updated Application', 'description' => ''],
        ],
    ])->assertRedirect('/admin/applications');

    expect($app->fresh()->is_active)->toBeFalse()
        ->and($app->fresh()->sort_order)->toBe(2)
        ->and($app->fresh()->translation('vi')?->name)->toBe('Ứng dụng đã sửa')
        ->and($app->fresh()->translation('en')?->name)->toBe('Updated Application');
});

it('can update a faq', function () {
    $faq = Faq::create(['sort_order' => 1, 'is_active' => true]);
    $faq->translations()->create(['locale' => 'vi', 'question' => 'Câu hỏi cũ?', 'answer' => 'Trả lời cũ']);

    $this->put("/admin/faqs/{$faq->id}", [
        'sort_order' => 3,
        'is_active' => 1,
        'translations' => [
            'vi' => ['question' => 'Câu hỏi mới?', 'answer' => 'Trả lời mới'],
            'en' => ['question' => 'New question?', 'answer' => 'New answer'],
        ],
    ])->assertRedirect('/admin/faqs');

    expect($faq->fresh()->translation('vi')?->question)->toBe('Câu hỏi mới?')
        ->and($faq->fresh()->translation('en')?->answer)->toBe('New answer');
});

it('can update a why-choose-us item', function () {
    $item = WhyChooseUs::create(['icon' => 'verified', 'sort_order' => 1, 'is_active' => true]);
    $item->translations()->create(['locale' => 'vi', 'title' => 'Tiêu đề cũ', 'description' => 'Mô tả cũ']);

    $this->put("/admin/why-choose-us/{$item->id}", [
        'icon' => 'star',
        'sort_order' => 5,
        'is_active' => 1,
        'translations' => [
            'vi' => ['title' => 'Tiêu đề mới', 'description' => 'Mô tả mới'],
            'en' => ['title' => 'New title', 'description' => ''],
        ],
    ])->assertRedirect('/admin/why-choose-us');

    expect($item->fresh()->icon)->toBe('star')
        ->and($item->fresh()->translation('vi')?->title)->toBe('Tiêu đề mới');
});

it('can update a news category', function () {
    $cat = NewsCategory::create(['slug' => 'danh-muc-cu', 'sort_order' => 1, 'is_active' => true]);
    $cat->translations()->create(['locale' => 'vi', 'name' => 'Danh mục cũ']);

    $this->put("/admin/news-categories/{$cat->id}", [
        'sort_order' => 4,
        'is_active' => 1,
        'translations' => [
            'vi' => ['name' => 'Danh mục mới'],
            'en' => ['name' => 'New Category'],
        ],
    ])->assertRedirect('/admin/news-categories');

    expect($cat->fresh()->translation('vi')?->name)->toBe('Danh mục mới')
        ->and($cat->fresh()->translation('en')?->name)->toBe('New Category');
});

it('can update a banner', function () {
    $banner = Banner::create(['section' => 'hero', 'sort_order' => 1, 'is_active' => true]);
    $banner->translations()->create(['locale' => 'vi', 'title' => 'Banner cũ', 'subtitle' => 'Phụ đề cũ']);

    $this->put("/admin/banners/{$banner->id}", [
        'section' => 'cta',
        'image' => null,
        'sort_order' => 2,
        'is_active' => 1,
        'translations' => [
            'vi' => ['title' => 'Banner mới', 'subtitle' => 'Phụ đề mới', 'text' => '', 'button_text' => 'Xem thêm'],
            'en' => ['title' => '', 'subtitle' => '', 'text' => '', 'button_text' => ''],
        ],
    ])->assertRedirect('/admin/banners');

    expect($banner->fresh()->section)->toBe('cta')
        ->and($banner->fresh()->translation('vi')?->title)->toBe('Banner mới');
});

it('can update a home stat', function () {
    $stat = HomeStat::create(['icon' => 'factory', 'value' => '21+', 'sort_order' => 1, 'is_active' => true]);
    $stat->translations()->create(['locale' => 'vi', 'label' => 'Nhãn cũ']);

    $this->put("/admin/home-stats/{$stat->id}", [
        'icon' => 'business',
        'value' => '30+',
        'sort_order' => 2,
        'is_active' => 1,
        'translations' => ['vi' => ['label' => 'Nhãn mới']],
    ])->assertRedirect('/admin/home-stats');

    expect($stat->fresh()->value)->toBe('30+')
        ->and($stat->fresh()->translation('vi')?->label)->toBe('Nhãn mới');
});

it('can toggle active state of an application via update', function () {
    $app = Application::create(['slug' => 'app-toggle', 'sort_order' => 1, 'is_active' => true]);
    $app->translations()->create(['locale' => 'vi', 'name' => 'Ứng dụng A', 'description' => '']);

    $this->put("/admin/applications/{$app->id}", [
        'sort_order' => 1,
        'is_active' => 0,
        'translations' => ['vi' => ['name' => 'Ứng dụng A', 'description' => '']],
    ])->assertRedirect('/admin/applications');

    expect($app->fresh()->is_active)->toBeFalse();
});

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
    $app = Application::create(['slug' => 'ung-dung-goc', 'name' => 'Ứng dụng ban đầu', 'description' => 'Mô tả cũ', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/applications/{$app->id}", [
        'name' => 'Ứng dụng đã sửa',
        'description' => 'Mô tả mới',
        'sort_order' => 2,
        'is_active' => 0,
    ])->assertRedirect('/admin/applications');

    expect($app->fresh()->is_active)->toBeFalse()
        ->and($app->fresh()->sort_order)->toBe(2)
        ->and($app->fresh()->name)->toBe('Ứng dụng đã sửa');
});

it('can update a faq', function () {
    $faq = Faq::create(['question' => 'Câu hỏi cũ?', 'answer' => 'Trả lời cũ', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/faqs/{$faq->id}", [
        'question' => 'Câu hỏi mới?',
        'answer' => 'Trả lời mới',
        'sort_order' => 3,
        'is_active' => 1,
    ])->assertRedirect('/admin/faqs');

    expect($faq->fresh()->question)->toBe('Câu hỏi mới?')
        ->and($faq->fresh()->answer)->toBe('Trả lời mới');
});

it('can update a why-choose-us item', function () {
    $item = WhyChooseUs::create(['icon' => 'verified', 'title' => 'Tiêu đề cũ', 'description' => 'Mô tả cũ', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/why-choose-us/{$item->id}", [
        'icon' => 'star',
        'title' => 'Tiêu đề mới',
        'description' => 'Mô tả mới',
        'sort_order' => 5,
        'is_active' => 1,
    ])->assertRedirect('/admin/why-choose-us');

    expect($item->fresh()->icon)->toBe('star')
        ->and($item->fresh()->title)->toBe('Tiêu đề mới');
});

it('can update a news category', function () {
    $cat = NewsCategory::create(['slug' => 'danh-muc-cu', 'name' => 'Danh mục cũ', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/news-categories/{$cat->id}", [
        'name' => 'Danh mục mới',
        'sort_order' => 4,
        'is_active' => 1,
    ])->assertRedirect('/admin/news-categories');

    expect($cat->fresh()->name)->toBe('Danh mục mới');
});

it('can update a banner', function () {
    $banner = Banner::create(['section' => 'hero', 'title' => 'Banner cũ', 'subtitle' => 'Phụ đề cũ', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/banners/{$banner->id}", [
        'section' => 'cta',
        'image' => null,
        'sort_order' => 2,
        'is_active' => 1,
        'title' => 'Banner mới',
        'subtitle' => 'Phụ đề mới',
        'text' => '',
        'button_text' => 'Xem thêm',
    ])->assertRedirect('/admin/banners');

    expect($banner->fresh()->section)->toBe('cta')
        ->and($banner->fresh()->title)->toBe('Banner mới');
});

it('can update a home stat', function () {
    $stat = HomeStat::create(['icon' => 'factory', 'value' => '21+', 'label' => 'Nhãn cũ', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/home-stats/{$stat->id}", [
        'icon' => 'business',
        'value' => '30+',
        'label' => 'Nhãn mới',
        'sort_order' => 2,
        'is_active' => 1,
    ])->assertRedirect('/admin/home-stats');

    expect($stat->fresh()->value)->toBe('30+')
        ->and($stat->fresh()->label)->toBe('Nhãn mới');
});

it('can toggle active state of an application via update', function () {
    $app = Application::create(['slug' => 'app-toggle', 'name' => 'Ứng dụng A', 'description' => '', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/applications/{$app->id}", [
        'name' => 'Ứng dụng A',
        'description' => '',
        'sort_order' => 1,
        'is_active' => 0,
    ])->assertRedirect('/admin/applications');

    expect($app->fresh()->is_active)->toBeFalse();
});
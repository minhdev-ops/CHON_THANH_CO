<?php

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('saves news content in both locales', function () {
    $category = NewsCategory::create(['slug' => 'ky-thuat', 'sort_order' => 1, 'is_active' => true]);

    $this->post('/admin/news', [
        'news_category_id' => $category->id,
        'image' => '/userfiles/images/news/content.png',
        'is_active' => 1,
        'translations' => [
            'vi' => [
                'title' => 'Bài viết nội dung',
                'excerpt' => 'Tóm tắt',
                'content' => "Đoạn đầu tiên.\n\nĐoạn thứ hai.",
            ],
            'en' => [
                'title' => 'Content article',
                'excerpt' => 'Excerpt',
                'content' => "First paragraph.\n\nSecond paragraph.",
            ],
        ],
    ])->assertRedirect('/admin/news');

    $news = News::where('slug', 'bai-viet-noi-dung')->first();
    expect($news)->not->toBeNull();
    expect($news->translation('vi')?->content)->toBe("Đoạn đầu tiên.\n\nĐoạn thứ hai.");
    expect($news->translation('en')?->content)->toBe("First paragraph.\n\nSecond paragraph.");
});

it('updates news content', function () {
    $news = News::create(['slug' => 'news-update-content', 'image' => '/x.png', 'is_active' => true]);
    $news->translations()->create([
        'locale' => 'vi',
        'title' => 'Cũ',
        'excerpt' => 'Tóm tắt cũ',
        'content' => 'Nội dung cũ',
    ]);

    $this->put("/admin/news/{$news->id}", [
        'image' => '/x.png',
        'is_active' => 1,
        'translations' => [
            'vi' => ['title' => 'Mới', 'excerpt' => 'Tóm tắt mới', 'content' => 'Nội dung mới'],
            'en' => ['title' => '', 'excerpt' => '', 'content' => ''],
        ],
    ])->assertRedirect('/admin/news');

    expect($news->fresh()->translation('vi')?->title)->toBe('Mới');
    expect($news->fresh()->translation('vi')?->content)->toBe('Nội dung mới');
});

it('can bulk delete news', function () {
    $n1 = News::create(['slug' => 'bulk-n1', 'image' => '/x.png', 'is_active' => true]);
    $n2 = News::create(['slug' => 'bulk-n2', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/news/bulk', ['ids' => [$n1->id, $n2->id], 'action' => 'delete'])
        ->assertRedirect('/admin/news');

    expect(News::find($n1->id))->toBeNull();
    expect(News::find($n2->id))->toBeNull();
});

it('can bulk deactivate and activate news', function () {
    $news = News::create(['slug' => 'bulk-toggle', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/news/bulk', ['ids' => [$news->id], 'action' => 'deactivate'])->assertRedirect('/admin/news');
    expect($news->fresh()->is_active)->toBeFalse();

    $this->post('/admin/news/bulk', ['ids' => [$news->id], 'action' => 'activate'])->assertRedirect('/admin/news');
    expect($news->fresh()->is_active)->toBeTrue();
});

it('rejects invalid news bulk action', function () {
    $this->post('/admin/news/bulk', ['ids' => [1], 'action' => 'hack'])->assertSessionHasErrors('action');
});

it('validates news required fields', function () {
    $this->post('/admin/news', [])->assertSessionHasErrors(['image', 'translations.vi.title', 'translations.vi.excerpt']);
});

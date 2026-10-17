<?php

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('saves news content', function () {
    $category = NewsCategory::create(['slug' => 'ky-thuat', 'name' => 'Kỹ thuật', 'sort_order' => 1, 'is_active' => true]);

    $this->post('/admin/news', [
        'news_category_id' => $category->id,
        'image' => '/userfiles/images/news/content.png',
        'is_active' => 1,
        'title' => 'Bài viết nội dung',
        'excerpt' => 'Tóm tắt',
        'content' => "Đoạn đầu tiên.\n\nĐoạn thứ hai.",
    ])->assertRedirect('/admin/news');

    $news = News::where('slug', 'bai-viet-noi-dung')->first();
    expect($news)->not->toBeNull();
    expect($news->content)->toBe("Đoạn đầu tiên.\n\nĐoạn thứ hai.");
});

it('updates news content', function () {
    $news = News::create(['slug' => 'news-update-content', 'title' => 'Cũ', 'excerpt' => 'Tóm tắt cũ', 'content' => 'Nội dung cũ', 'image' => '/x.png', 'is_active' => true]);

    $this->put("/admin/news/{$news->id}", [
        'image' => '/x.png',
        'is_active' => 1,
        'title' => 'Mới',
        'excerpt' => 'Tóm tắt mới',
        'content' => 'Nội dung mới',
    ])->assertRedirect('/admin/news');

    expect($news->fresh()->title)->toBe('Mới');
    expect($news->fresh()->content)->toBe('Nội dung mới');
});

it('can bulk delete news', function () {
    $n1 = News::create(['slug' => 'bulk-n1', 'title' => 'N1', 'excerpt' => 'Tóm tắt', 'image' => '/x.png', 'is_active' => true]);
    $n2 = News::create(['slug' => 'bulk-n2', 'title' => 'N2', 'excerpt' => 'Tóm tắt', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/news/bulk', ['ids' => [$n1->id, $n2->id], 'action' => 'delete'])
        ->assertRedirect('/admin/news');

    expect(News::find($n1->id))->toBeNull();
    expect(News::find($n2->id))->toBeNull();
});

it('can bulk deactivate and activate news', function () {
    $news = News::create(['slug' => 'bulk-toggle', 'title' => 'Toggle', 'excerpt' => 'Tóm tắt', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/news/bulk', ['ids' => [$news->id], 'action' => 'deactivate'])->assertRedirect('/admin/news');
    expect($news->fresh()->is_active)->toBeFalse();

    $this->post('/admin/news/bulk', ['ids' => [$news->id], 'action' => 'activate'])->assertRedirect('/admin/news');
    expect($news->fresh()->is_active)->toBeTrue();
});

it('rejects invalid news bulk action', function () {
    $this->post('/admin/news/bulk', ['ids' => [1], 'action' => 'hack'])->assertSessionHasErrors('action');
});

it('validates news required fields', function () {
    $this->post('/admin/news', [])->assertSessionHasErrors(['image', 'title', 'excerpt']);
});
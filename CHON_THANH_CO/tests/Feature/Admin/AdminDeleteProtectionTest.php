<?php

use App\Models\Application;
use App\Models\Category;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('blocks deleting a category that contains products', function () {
    $category = Category::create(['slug' => 'cat-in-use', 'sort_order' => 1, 'is_active' => true]);
    Product::create(['category_id' => $category->id, 'slug' => 'p-in-use', 'code' => 'INUSE-1', 'image' => '/x.png', 'is_active' => true]);

    $this->delete("/admin/categories/{$category->id}")
        ->assertRedirect()
        ->assertSessionHas('error', 'Không thể xóa danh mục đang chứa sản phẩm.');

    expect(Category::find($category->id))->not->toBeNull();
});

it('allows deleting an empty category', function () {
    $category = Category::create(['slug' => 'cat-empty', 'sort_order' => 1, 'is_active' => true]);

    $this->delete("/admin/categories/{$category->id}")->assertRedirect('/admin/categories');

    expect(Category::find($category->id))->toBeNull();
});

it('blocks deleting an application attached to products', function () {
    $category = Category::create(['slug' => 'cat-app', 'sort_order' => 1, 'is_active' => true]);
    $app = Application::create(['slug' => 'app-in-use', 'sort_order' => 1, 'is_active' => true]);

    $product = Product::create(['category_id' => $category->id, 'slug' => 'p-app', 'code' => 'APP-1', 'image' => '/x.png', 'is_active' => true]);
    $product->applications()->attach($app->id);

    $this->delete("/admin/applications/{$app->id}")
        ->assertRedirect()
        ->assertSessionHas('error', 'Không thể xóa ứng dụng đang được gắn với sản phẩm.');

    expect(Application::find($app->id))->not->toBeNull();
});

it('allows deleting an unused application', function () {
    $app = Application::create(['slug' => 'app-unused', 'sort_order' => 1, 'is_active' => true]);

    $this->delete("/admin/applications/{$app->id}")->assertRedirect('/admin/applications');

    expect(Application::find($app->id))->toBeNull();
});

it('blocks deleting a news category that contains articles', function () {
    $category = NewsCategory::create(['slug' => 'news-cat-in-use', 'sort_order' => 1, 'is_active' => true]);
    News::create(['news_category_id' => $category->id, 'slug' => 'n-in-use', 'image' => '/x.png', 'is_active' => true]);

    $this->delete("/admin/news-categories/{$category->id}")
        ->assertRedirect()
        ->assertSessionHas('error', 'Không thể xóa danh mục đang chứa bài viết.');

    expect(NewsCategory::find($category->id))->not->toBeNull();
});

it('allows deleting an empty news category', function () {
    $category = NewsCategory::create(['slug' => 'news-cat-empty', 'sort_order' => 1, 'is_active' => true]);

    $this->delete("/admin/news-categories/{$category->id}")->assertRedirect('/admin/news-categories');

    expect(NewsCategory::find($category->id))->toBeNull();
});

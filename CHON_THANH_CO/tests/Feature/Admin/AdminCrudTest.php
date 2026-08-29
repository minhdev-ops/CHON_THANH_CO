<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\HomeStat;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Product;
use App\Models\Project;
use App\Models\WhyChooseUs;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** Đăng nhập admin cho mọi test trong file này. */
beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('can list every content module', function () {
    foreach (['products', 'projects', 'news', 'certificates', 'banners', 'faqs', 'categories', 'applications', 'home-stats', 'why-choose-us', 'news-categories'] as $path) {
        $this->get("/admin/{$path}")->assertStatus(200);
    }
});

it('can open create forms for every module', function () {
    foreach (['products', 'projects', 'news', 'certificates', 'banners', 'faqs', 'categories', 'applications', 'home-stats', 'why-choose-us', 'news-categories'] as $path) {
        $this->get("/admin/{$path}/create")->assertStatus(200);
    }
});

it('can create, edit, update and delete a category', function () {
    $this->post('/admin/categories', [
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => [
            'vi' => ['name' => 'Vải địa kỹ thuật', 'description' => 'Mô tả'],
            'en' => ['name' => 'Geotextile', 'description' => 'Desc'],
        ],
    ])->assertRedirect('/admin/categories');

    $category = Category::where('slug', 'vai-dia-ky-thuat')->first();
    expect($category)->not->toBeNull();
    expect($category->translation('vi')?->name)->toBe('Vải địa kỹ thuật');

    // Sửa
    $this->get("/admin/categories/{$category->id}/edit")->assertStatus(200);
    $this->put("/admin/categories/{$category->id}", [
        'sort_order' => 2,
        'is_active' => 0,
        'translations' => [
            'vi' => ['name' => 'Vải địa kỹ thuật sửa', 'description' => 'Mô tả 2'],
            'en' => ['name' => 'Geotextile Updated', 'description' => ''],
        ],
    ])->assertRedirect('/admin/categories');

    expect($category->fresh()->is_active)->toBeFalse();
    expect($category->fresh()->translation('vi')?->name)->toBe('Vải địa kỹ thuật sửa');

    // Xóa
    $this->delete("/admin/categories/{$category->id}")->assertRedirect('/admin/categories');
    expect(Category::find($category->id))->toBeNull();
});

it('can create and delete an application', function () {
    $this->post('/admin/applications', [
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => ['vi' => ['name' => 'Ứng dụng đường bộ', 'description' => 'Mô tả']],
    ])->assertRedirect('/admin/applications');

    $app = Application::where('slug', 'ung-dung-duong-bo')->first();
    expect($app)->not->toBeNull();

    $this->delete("/admin/applications/{$app->id}")->assertRedirect('/admin/applications');
    expect(Application::find($app->id))->toBeNull();
});

it('can create and delete a faq', function () {
    $this->post('/admin/faqs', [
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => [
            'vi' => ['question' => 'FAQ hỏi gì?', 'answer' => 'Câu trả lời'],
            'en' => ['question' => 'What?', 'answer' => 'Answer'],
        ],
    ])->assertRedirect('/admin/faqs');

    $faq = Faq::first();
    expect($faq)->not->toBeNull();
    expect($faq->translation('vi')?->question)->toBe('FAQ hỏi gì?');

    $this->delete("/admin/faqs/{$faq->id}")->assertRedirect('/admin/faqs');
    expect(Faq::find($faq->id))->toBeNull();
});

it('can create and update a home stat', function () {
    $this->post('/admin/home-stats', [
        'icon' => 'factory',
        'value' => '31',
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => ['vi' => ['label' => 'Ngành nghề kinh doanh']],
    ])->assertRedirect('/admin/home-stats');

    $stat = HomeStat::first();
    expect($stat)->not->toBeNull();
    expect($stat->value)->toBe('31');

    $this->put("/admin/home-stats/{$stat->id}", [
        'icon' => 'factory',
        'value' => '32',
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => ['vi' => ['label' => 'Ngành nghề kinh doanh']],
    ])->assertRedirect('/admin/home-stats');

    expect($stat->fresh()->value)->toBe('32');
});

it('can create and delete a why-choose-us item', function () {
    $this->post('/admin/why-choose-us', [
        'icon' => 'verified',
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => [
            'vi' => ['title' => 'Tiêu chuẩn quốc tế', 'description' => 'Mô tả'],
            'en' => ['title' => 'International standard', 'description' => 'Desc'],
        ],
    ])->assertRedirect('/admin/why-choose-us');

    $item = WhyChooseUs::first();
    expect($item)->not->toBeNull();
    expect($item->translation('vi')?->title)->toBe('Tiêu chuẩn quốc tế');

    $this->delete("/admin/why-choose-us/{$item->id}")->assertRedirect('/admin/why-choose-us');
    expect(WhyChooseUs::find($item->id))->toBeNull();
});

it('can create and delete a news category', function () {
    $this->post('/admin/news-categories', [
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => ['vi' => ['name' => 'Tin kỹ thuật'], 'en' => ['name' => 'Technical News']],
    ])->assertRedirect('/admin/news-categories');

    $cat = NewsCategory::where('slug', 'tin-ky-thuat')->first();
    expect($cat)->not->toBeNull();

    $this->delete("/admin/news-categories/{$cat->id}")->assertRedirect('/admin/news-categories');
    expect(NewsCategory::find($cat->id))->toBeNull();
});

it('can create, edit, update and delete a certificate', function () {
    $this->post('/admin/certificates', [
        'image' => '/userfiles/images/certificates/test.png',
        'file' => null,
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => [
            'vi' => ['name' => 'ISO 9001:2015', 'description' => 'Mô tả'],
            'en' => ['name' => 'ISO 9001:2015', 'description' => 'Desc'],
        ],
    ])->assertRedirect('/admin/certificates');

    $cert = Certificate::first();
    expect($cert)->not->toBeNull();

    $this->put("/admin/certificates/{$cert->id}", [
        'image' => '/userfiles/images/certificates/test2.png',
        'file' => null,
        'sort_order' => 2,
        'is_active' => 1,
        'translations' => ['vi' => ['name' => 'ISO 9001:2015 sửa', 'description' => 'Mô tả'], 'en' => ['name' => '', 'description' => '']],
    ])->assertRedirect('/admin/certificates');

    expect($cert->fresh()->image)->toBe('/userfiles/images/certificates/test2.png');

    $this->delete("/admin/certificates/{$cert->id}")->assertRedirect('/admin/certificates');
    expect(Certificate::find($cert->id))->toBeNull();
});

it('can create and delete a banner', function () {
    $this->post('/admin/banners', [
        'section' => 'hero',
        'image' => null,
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => [
            'vi' => ['title' => 'Vật liệu địa kỹ thuật', 'subtitle' => 'Phụ đề', 'text' => '', 'button_text' => 'Xem thêm'],
            'en' => ['title' => '', 'subtitle' => '', 'text' => '', 'button_text' => ''],
        ],
    ])->assertRedirect('/admin/banners');

    $banner = Banner::where('section', 'hero')->first();
    expect($banner)->not->toBeNull();

    $this->delete("/admin/banners/{$banner->id}")->assertRedirect('/admin/banners');
    expect(Banner::find($banner->id))->toBeNull();
});

it('can create, update and delete a news article', function () {
    $category = NewsCategory::create(['slug' => 'tin-tuc', 'sort_order' => 1, 'is_active' => true]);

    $this->post('/admin/news', [
        'news_category_id' => $category->id,
        'image' => '/userfiles/images/news/test.png',
        'is_active' => 1,
        'translations' => [
            'vi' => ['title' => 'Bài viết kiểm thử', 'excerpt' => 'Tóm tắt bài viết'],
            'en' => ['title' => '', 'excerpt' => ''],
        ],
    ])->assertRedirect('/admin/news');

    $news = News::where('slug', 'bai-viet-kiem-thu')->first();
    expect($news)->not->toBeNull();

    $this->put("/admin/news/{$news->id}", [
        'news_category_id' => $category->id,
        'image' => '/userfiles/images/news/test2.png',
        'is_active' => 0,
        'translations' => [
            'vi' => ['title' => 'Bài viết kiểm thử sửa', 'excerpt' => 'Tóm tắt mới'],
            'en' => ['title' => '', 'excerpt' => ''],
        ],
    ])->assertRedirect('/admin/news');

    expect($news->fresh()->is_active)->toBeFalse();

    $this->delete("/admin/news/{$news->id}")->assertRedirect('/admin/news');
    expect(News::find($news->id))->toBeNull();
});

it('can create, update and delete a project', function () {
    $this->post('/admin/projects', [
        'period' => '2024',
        'area' => '12.000 m²',
        'hero_image' => '/userfiles/images/projects/test.png',
        'is_active' => 1,
        'sort_order' => 1,
        'translations' => [
            'vi' => ['name' => 'Dự án đường cao tốc', 'location' => 'Đồng Nai', 'description' => 'Mô tả dự án'],
            'en' => ['name' => '', 'location' => '', 'description' => ''],
        ],
    ])->assertRedirect('/admin/projects');

    $project = Project::where('slug', 'du-an-duong-cao-toc')->first();
    expect($project)->not->toBeNull();

    $this->put("/admin/projects/{$project->id}", [
        'period' => '2025',
        'area' => '15.000 m²',
        'hero_image' => '/userfiles/images/projects/test2.png',
        'is_active' => 1,
        'sort_order' => 2,
        'translations' => [
            'vi' => ['name' => 'Dự án đường cao tốc sửa', 'location' => 'Đồng Nai', 'description' => 'Mô tả mới'],
            'en' => ['name' => '', 'location' => '', 'description' => ''],
        ],
    ])->assertRedirect('/admin/projects');

    expect($project->fresh()->period)->toBe('2025');

    $this->delete("/admin/projects/{$project->id}")->assertRedirect('/admin/projects');
    expect(Project::find($project->id))->toBeNull();
});

it('can create, update and delete a product', function () {
    $category = Category::create(['slug' => 'vai-kt-khong-det', 'sort_order' => 1, 'is_active' => true]);
    $category->translations()->create(['locale' => 'vi', 'name' => 'Vải không dệt']);

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'code' => 'ART 12',
        'image' => '/userfiles/images/products/test.png',
        'strength_min' => 12,
        'strength_max' => 12,
        'is_featured' => 1,
        'is_active' => 1,
        'sort_order' => 1,
        'translations' => [
            'vi' => ['name' => 'Vải địa kỹ thuật ART 12', 'description' => 'Mô tả sản phẩm'],
            'en' => ['name' => '', 'description' => ''],
        ],
    ])->assertRedirect('/admin/products');

    $product = Product::where('code', 'ART 12')->first();
    expect($product)->not->toBeNull();
    expect($product->translation('vi')?->name)->toBe('Vải địa kỹ thuật ART 12');

    $this->put("/admin/products/{$product->id}", [
        'category_id' => $category->id,
        'code' => 'ART 12',
        'image' => '/userfiles/images/products/test2.png',
        'strength_min' => 12,
        'strength_max' => 15,
        'is_featured' => 0,
        'is_active' => 1,
        'sort_order' => 2,
        'translations' => [
            'vi' => ['name' => 'Vải địa kỹ thuật ART 12 sửa', 'description' => 'Mô tả mới'],
            'en' => ['name' => '', 'description' => ''],
        ],
    ])->assertRedirect('/admin/products');

    // strength_max là decimal:2 nên trả về chuỗi '15.00'
    expect($product->fresh()->strength_max)->toBe('15.00');

    $this->delete("/admin/products/{$product->id}")->assertRedirect('/admin/products');
    expect(Product::find($product->id))->toBeNull();
});

it('validates required fields when creating a product', function () {
    $this->post('/admin/products', [])->assertSessionHasErrors(['code', 'image', 'category_id', 'translations.vi.name', 'translations.vi.description']);
});

it('can bulk delete products', function () {
    $category = Category::create(['slug' => 'cat-1', 'sort_order' => 1, 'is_active' => true]);

    $p1 = Product::create(['category_id' => $category->id, 'slug' => 'p-1', 'code' => 'P1', 'image' => '/x.png', 'is_active' => true]);
    $p2 = Product::create(['category_id' => $category->id, 'slug' => 'p-2', 'code' => 'P2', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/products/bulk', [
        'ids' => [$p1->id, $p2->id],
        'action' => 'delete',
    ])->assertRedirect('/admin/products');

    expect(Product::find($p1->id))->toBeNull();
    expect(Product::find($p2->id))->toBeNull();
});

it('can bulk deactivate and activate products', function () {
    $category = Category::create(['slug' => 'cat-2', 'sort_order' => 1, 'is_active' => true]);

    $p = Product::create(['category_id' => $category->id, 'slug' => 'p-3', 'code' => 'P3', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/products/bulk', ['ids' => [$p->id], 'action' => 'deactivate'])->assertRedirect('/admin/products');
    expect($p->fresh()->is_active)->toBeFalse();

    $this->post('/admin/products/bulk', ['ids' => [$p->id], 'action' => 'activate'])->assertRedirect('/admin/products');
    expect($p->fresh()->is_active)->toBeTrue();
});

it('can bulk feature and unfeature products', function () {
    $category = Category::create(['slug' => 'cat-feat', 'sort_order' => 1, 'is_active' => true]);

    $p = Product::create(['category_id' => $category->id, 'slug' => 'p-feat', 'code' => 'PFEAT', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/products/bulk', ['ids' => [$p->id], 'action' => 'feature'])->assertRedirect('/admin/products');
    expect($p->fresh()->is_featured)->toBeTrue();

    $this->post('/admin/products/bulk', ['ids' => [$p->id], 'action' => 'unfeature'])->assertRedirect('/admin/products');
    expect($p->fresh()->is_featured)->toBeFalse();
});

it('rejects invalid bulk action', function () {
    $this->post('/admin/products/bulk', ['ids' => [1], 'action' => 'hack'])->assertSessionHasErrors('action');
});

it('logs audit entries for bulk actions', function () {
    $category = Category::create(['slug' => 'cat-audit', 'sort_order' => 1, 'is_active' => true]);

    $p1 = Product::create(['category_id' => $category->id, 'slug' => 'p-a1', 'code' => 'PA1', 'image' => '/x.png', 'is_active' => true]);
    $p2 = Product::create(['category_id' => $category->id, 'slug' => 'p-a2', 'code' => 'PA2', 'image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/products/bulk', [
        'ids' => [$p1->id, $p2->id],
        'action' => 'deactivate',
    ])->assertRedirect('/admin/products');

    $this->post('/admin/products/bulk', [
        'ids' => [$p1->id],
        'action' => 'delete',
    ])->assertRedirect('/admin/products');

    expect(AuditLog::where('model_type', Product::class)->where('action', 'updated')->where('model_id', $p1->id)->count())->toBe(1);
    expect(AuditLog::where('model_type', Product::class)->where('action', 'updated')->where('model_id', $p2->id)->count())->toBe(1);
    expect(AuditLog::where('model_type', Product::class)->where('action', 'deleted')->where('model_id', $p1->id)->count())->toBe(1);
});

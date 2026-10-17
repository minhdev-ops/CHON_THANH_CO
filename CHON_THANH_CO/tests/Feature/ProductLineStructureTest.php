<?php

use App\Models\Application;
use App\Models\Category;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Services\ContentService;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

function productLine(): Category
{
    return Category::create([
        'slug' => 'mang-chong-tham',
        'name' => 'Màng chống thấm',
        'sort_order' => 1,
        'is_active' => true,
    ]);
}

function makeProduct(array $attrs = []): Product
{
    return Product::create(array_merge([
        'category_id' => productLine()->id,
        'slug' => 'sp-'.uniqid(),
        'code' => 'SP-'.uniqid(),
        'name' => 'Sản phẩm',
        'description' => 'Mô tả',
        'image' => '/userfiles/images/products/a.png',
        'is_active' => true,
    ], $attrs));
}

it('saves a product as a variant of a line', function () {
    $parent = makeProduct(['code' => 'HDPE', 'name' => 'Màng chống thấm HDPE']);

    $this->post('/admin/products', [
        'category_id' => $parent->category_id,
        'parent_id' => $parent->id,
        'code' => 'HDPE-05',
        'name' => 'Màng chống thấm HDPE 0.5mm',
        'description' => 'Mô tả',
        'image' => '/userfiles/images/products/b.png',
        'is_active' => 1,
    ])->assertSessionHasNoErrors();

    $child = Product::where('code', 'HDPE-05')->firstOrFail();

    expect($child->parent_id)->toBe($parent->id)
        ->and($parent->fresh()->variants()->pluck('id')->all())->toBe([$child->id]);
});

it('refuses to make a product the parent of its own variant', function () {
    $parent = makeProduct(['code' => 'HDPE', 'name' => 'Màng chống thấm HDPE']);
    $child = makeProduct(['code' => 'HDPE-05', 'parent_id' => $parent->id]);

    // Chọn con làm cha sẽ tạo vòng lặp, phải bị chặn.
    $this->put("/admin/products/{$parent->id}", [
        'category_id' => $parent->category_id,
        'parent_id' => $child->id,
        'code' => 'HDPE',
        'name' => 'Màng chống thấm HDPE',
        'description' => 'Mô tả',
        'image' => '/userfiles/images/products/a.png',
        'is_active' => 1,
    ])->assertSessionHasErrors('parent_id');

    expect($parent->fresh()->parent_id)->toBeNull();
});

it('saves strength grades for a product line', function () {
    $product = makeProduct(['code' => 'ART', 'name' => 'Vải địa kỹ thuật không dệt ART']);

    $this->put("/admin/products/{$product->id}", [
        'category_id' => $product->category_id,
        'code' => 'ART',
        'name' => 'Vải địa kỹ thuật không dệt ART',
        'description' => 'Mô tả',
        'image' => '/userfiles/images/products/a.png',
        'is_active' => 1,
        'strength_grades' => [
            ['grade' => '12', 'unit' => 'kN/m', 'sort_order' => 0],
            ['grade' => '25', 'unit' => 'kN/m', 'sort_order' => 1],
        ],
    ])->assertSessionHasNoErrors();

    expect($product->fresh()->strengthGrades()->pluck('grade')->all())->toBe(['12', '25']);
});

it('drops a strength grade row with an empty grade instead of failing the save', function () {
    $product = makeProduct(['code' => 'GET', 'name' => 'Vải địa kỹ thuật dệt GET']);

    $this->put("/admin/products/{$product->id}", [
        'category_id' => $product->category_id,
        'code' => 'GET',
        'name' => 'Vải địa kỹ thuật dệt GET',
        'description' => 'Mô tả',
        'image' => '/userfiles/images/products/a.png',
        'is_active' => 1,
        'strength_grades' => [
            ['grade' => '20 - 200', 'unit' => 'g/m²', 'sort_order' => 0],
            ['grade' => '', 'unit' => 'kN/m', 'sort_order' => 1],
        ],
    ])->assertSessionHasNoErrors();

    expect($product->fresh()->strengthGrades()->pluck('grade')->all())->toBe(['20 - 200']);
});

it('returns variants, strength grades, related products and projects for a detail page', function () {
    $parent = makeProduct(['code' => 'HDPE', 'name' => 'Màng chống thấm HDPE']);
    $child = makeProduct(['code' => 'HDPE-05', 'name' => 'HDPE 0.5mm', 'parent_id' => $parent->id]);

    $child->strengthGrades()->create(['grade' => '12', 'unit' => 'kN/m', 'sort_order' => 0]);

    $project = Project::create([
        'slug' => 'san-bay-long-thanh',
        'name' => 'Sân bay Long Thành',
        'location' => 'Đồng Nai',
        'period' => '2025',
        'hero_image' => '/userfiles/images/projects/a.jpg',
        'description' => 'Mô tả',
        'is_active' => true,
    ]);
    ProjectMaterial::create([
        'project_id' => $project->id,
        'name' => 'Màng chống thấm HDPE',
        'detail' => '0.5mm',
        'image' => '/userfiles/images/materials/a.jpg',
        'product_id' => $parent->id,
    ]);

    $response = $this->getJson('/api/v1/products/'.$child->slug)->assertOk();
    $data = $response->json('data');

    // Bản đang xem phải nằm trong danh sách mã cùng dòng và được đánh dấu.
    expect(collect($data['variants'])->pluck('slug')->all())->toContain($child->slug);

    expect(collect($data['strength_grades'])->pluck('grade')->all())->toBe(['12']);

    // Công trình gắn với sản phẩm cha vẫn phải hiện khi xem biến thể con.
    expect(collect($data['projects'])->pluck('slug')->all())->toBe(['san-bay-long-thanh']);

    // Sản phẩm đang xem không được trả về chính nó trong danh sách liên quan.
    expect(collect($data['related_products'])->pluck('slug')->all())->not->toContain($child->slug);
});

it('never returns the product being viewed in its own related list', function () {
    $service = app(ProductService::class);
    $product = makeProduct(['code' => 'ART', 'name' => 'Vải ART']);

    // Thêm nhiều sản phẩm cùng ứng dụng để chắc chắn có ứng viên liên quan.
    $app = Application::create(['slug' => 'chong-tham', 'name' => 'Chống thấm', 'sort_order' => 1, 'is_active' => true]);
    $product->applications()->attach($app->id);
    foreach (range(1, 4) as $i) {
        makeProduct(['code' => "REL-{$i}"])->applications()->attach($app->id);
    }

    expect($service->related($product, 8)->pluck('id')->all())->not->toContain($product->id);
});

it('sorts news by published date rather than insertion order', function () {
    $category = NewsCategory::create(['slug' => 'tin-tuc', 'name' => 'Tin tức', 'sort_order' => 1, 'is_active' => true]);

    // Bài tạo sau nhưng đăng trước vẫn phải lên đầu.
    $older = News::create([
        'news_category_id' => $category->id,
        'slug' => 'bai-cu',
        'title' => 'Bài cũ',
        'excerpt' => 'Tóm tắt',
        'content' => 'Nội dung',
        'image' => '/userfiles/images/news/a.jpg',
        'published_at' => now()->subDays(10),
        'is_active' => true,
    ]);
    $newer = News::create([
        'news_category_id' => $category->id,
        'slug' => 'bai-moi',
        'title' => 'Bài mới',
        'excerpt' => 'Tóm tắt',
        'content' => 'Nội dung',
        'image' => '/userfiles/images/news/b.jpg',
        'published_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $result = app(ContentService::class)->news([], 'vi', 12);

    expect($result['items']->pluck('slug')->all())->toBe([$newer->slug, $older->slug]);
});

it('paginates news without skipping or repeating items', function () {
    $category = NewsCategory::create(['slug' => 'tin-tuc', 'name' => 'Tin tức', 'sort_order' => 1, 'is_active' => true]);

    foreach (range(1, 7) as $i) {
        News::create([
            'news_category_id' => $category->id,
            'slug' => "bai-{$i}",
            'title' => "Bài {$i}",
            'excerpt' => 'Tóm tắt',
            'content' => 'Nội dung',
            'image' => '/userfiles/images/news/a.jpg',
            // Ngày đăng không đồng đều với id để bắt lỗi cursor chỉ theo id.
            'published_at' => now()->subDays(10 - $i),
            'is_active' => true,
        ]);
    }

    $service = app(ContentService::class);
    $collected = [];
    $cursor = null;

    // Duyệt hết các trang như frontend đang làm.
    for ($page = 0; $page < 10; $page++) {
        $result = $service->news($cursor ? ['cursor' => $cursor] : [], 'vi', 2);
        $collected = array_merge($collected, $result['items']->pluck('slug')->all());
        $cursor = $result['next_cursor'];

        if ($cursor === null) {
            break;
        }
    }

    expect($collected)->toHaveCount(7)
        ->and(array_unique($collected))->toHaveCount(7)
        ->and($collected[0])->toBe('bai-7');
});

it('saves a news article submitted from the Vietnamese tab', function () {
    $category = NewsCategory::create(['slug' => 'tin-tuc', 'name' => 'Tin tức', 'sort_order' => 1, 'is_active' => true]);

    // Form admin gửi field dạng translations[vi][...]; nếu controller không
    // copy sang khóa thường thì validate() báo thiếu title/excerpt.
    $this->post('/admin/news', [
        'news_category_id' => $category->id,
        'image' => '/userfiles/images/news/a.jpg',
        'is_active' => 1,
        'translations' => [
            'vi' => [
                'title' => 'Tiêu đề tiếng Việt',
                'excerpt' => 'Tóm tắt tiếng Việt',
                'content' => 'Nội dung tiếng Việt',
            ],
        ],
    ])->assertSessionHasNoErrors();

    $news = News::where('title', 'Tiêu đề tiếng Việt')->first();

    expect($news)->not->toBeNull()
        ->and($news->title)->toBe('Tiêu đề tiếng Việt')
        ->and($news->excerpt)->toBe('Tóm tắt tiếng Việt')
        ->and($news->content)->toBe('Nội dung tiếng Việt');
});

it('saves a news article submitted from the Vietnamese tab when editing', function () {
    $category = NewsCategory::create(['slug' => 'tin-tuc', 'name' => 'Tin tức', 'sort_order' => 1, 'is_active' => true]);
    $news = News::create([
        'news_category_id' => $category->id,
        'slug' => 'bai-cu',
        'title' => 'Tiêu đề cũ',
        'excerpt' => 'Tóm tắt cũ',
        'content' => 'Nội dung cũ',
        'image' => '/userfiles/images/news/a.jpg',
        'published_at' => now(),
        'is_active' => true,
    ]);

    $this->put("/admin/news/{$news->id}", [
        'news_category_id' => $category->id,
        'image' => '/userfiles/images/news/a.jpg',
        'is_active' => 1,
        'translations' => [
            'vi' => [
                'title' => 'Tiêu đề đã sửa',
                'excerpt' => 'Tóm tắt đã sửa',
                'content' => 'Nội dung đã sửa',
            ],
        ],
    ])->assertSessionHasNoErrors();

    expect($news->fresh()->title)->toBe('Tiêu đề đã sửa');
});

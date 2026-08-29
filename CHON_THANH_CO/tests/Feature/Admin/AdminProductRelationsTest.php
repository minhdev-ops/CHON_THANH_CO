<?php

use App\Models\Application;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('saves product specs with translations', function () {
    $category = Category::create(['slug' => 'vai-khong-det', 'sort_order' => 1, 'is_active' => true]);
    $category->translations()->create(['locale' => 'vi', 'name' => 'Vải không dệt']);

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'code' => 'ART-SPEC-1',
        'image' => '/userfiles/images/products/spec.png',
        'is_active' => 1,
        'translations' => [
            'vi' => ['name' => 'Vải ART SPEC 1', 'description' => 'Mô tả'],
            'en' => ['name' => '', 'description' => ''],
        ],
        'specs' => [
            [
                'value' => '30 kN/m',
                'icon' => 'bolt',
                'label_vi' => 'Cường độ kéo',
                'label_en' => 'Tensile strength',
                'sort_order' => 1,
            ],
            [
                'value' => '200 g/m²',
                'icon' => 'weight',
                'label_vi' => 'Định lượng',
                'label_en' => '',
                'sort_order' => 2,
            ],
        ],
        'images' => [
            ['image' => '/userfiles/images/products/spec-2.png', 'alt' => 'Ảnh 2', 'sort_order' => 1],
        ],
        'applications' => [],
    ])->assertRedirect('/admin/products');

    $product = Product::where('code', 'ART-SPEC-1')->first();
    expect($product)->not->toBeNull();

    expect($product->specs()->count())->toBe(2);
    $spec = $product->specs()->first();
    expect($spec->value)->toBe('30 kN/m')
        ->and($spec->icon)->toBe('bolt')
        ->and($spec->translation('vi')?->label)->toBe('Cường độ kéo')
        ->and($spec->translation('en')?->label)->toBe('Tensile strength');

    expect($product->images()->count())->toBe(1);
    expect($product->images()->first()->image)->toBe('/userfiles/images/products/spec-2.png');
});

it('syncs applications when creating a product', function () {
    $category = Category::create(['slug' => 'cat-app', 'sort_order' => 1, 'is_active' => true]);
    $category->translations()->create(['locale' => 'vi', 'name' => 'Danh mục']);

    $app1 = Application::create(['slug' => 'phan-cach-loc', 'sort_order' => 1, 'is_active' => true]);
    $app2 = Application::create(['slug' => 'thoat-nuoc', 'sort_order' => 2, 'is_active' => true]);

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'code' => 'ART-APP-1',
        'image' => '/userfiles/images/products/app.png',
        'is_active' => 1,
        'applications' => [$app1->id, $app2->id],
        'translations' => [
            'vi' => ['name' => 'Vải ART APP 1', 'description' => 'Mô tả'],
            'en' => ['name' => '', 'description' => ''],
        ],
    ])->assertRedirect('/admin/products');

    $product = Product::where('code', 'ART-APP-1')->first();
    expect($product->applications()->pluck('applications.id')->sort()->values()->all())
        ->toBe(collect([$app1->id, $app2->id])->sort()->values()->all());
});

it('replaces relations when updating a product', function () {
    $category = Category::create(['slug' => 'cat-replace', 'sort_order' => 1, 'is_active' => true]);
    $category->translations()->create(['locale' => 'vi', 'name' => 'Danh mục']);

    $app1 = Application::create(['slug' => 'ung-dung-1', 'sort_order' => 1, 'is_active' => true]);
    $app2 = Application::create(['slug' => 'ung-dung-2', 'sort_order' => 2, 'is_active' => true]);

    $product = Product::create([
        'category_id' => $category->id,
        'slug' => 'product-replace',
        'code' => 'REPLACE-1',
        'image' => '/x.png',
        'is_active' => true,
    ]);
    $product->applications()->attach($app1->id);
    $product->specs()->create(['value' => 'Cũ', 'sort_order' => 1]);
    $product->images()->create(['image' => '/old.png', 'sort_order' => 1]);

    $this->put("/admin/products/{$product->id}", [
        'category_id' => $category->id,
        'code' => 'REPLACE-1',
        'image' => '/x.png',
        'is_active' => 1,
        'applications' => [$app2->id],
        'translations' => [
            'vi' => ['name' => 'Đã thay đổi', 'description' => 'Mô tả'],
            'en' => ['name' => '', 'description' => ''],
        ],
        'specs' => [
            ['value' => 'Mới', 'icon' => '', 'label_vi' => 'Nhãn mới', 'label_en' => '', 'sort_order' => 1],
        ],
        'images' => [
            ['image' => '/new.png', 'alt' => '', 'sort_order' => 1],
        ],
    ])->assertRedirect('/admin/products');

    $product->refresh();

    // Quan hệ cũ phải bị thay thế hoàn toàn
    expect($product->applications()->pluck('applications.id')->all())->toBe([$app2->id]);
    expect($product->specs()->count())->toBe(1);
    expect($product->specs()->first()->value)->toBe('Mới');
    expect($product->images()->count())->toBe(1);
    expect($product->images()->first()->image)->toBe('/new.png');
});

it('validates specs value max length', function () {
    $category = Category::create(['slug' => 'cat-validate', 'sort_order' => 1, 'is_active' => true]);
    $category->translations()->create(['locale' => 'vi', 'name' => 'Danh mục']);

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'code' => 'VALIDATE-1',
        'image' => '/x.png',
        'is_active' => 1,
        'translations' => ['vi' => ['name' => 'Tên', 'description' => 'Mô tả']],
        'specs' => [
            ['value' => str_repeat('a', 101), 'icon' => '', 'label_vi' => '', 'label_en' => '', 'sort_order' => 1],
        ],
    ])->assertSessionHasErrors('specs.0.value');
});

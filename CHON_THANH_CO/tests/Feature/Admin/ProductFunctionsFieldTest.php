<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);

    $this->category = Category::create([
        'slug' => 'vai-kt-det',
        'name' => 'Vải địa kỹ thuật dệt',
        'sort_order' => 2,
        'is_active' => true,
    ]);
});

/**
 * Payload tối thiểu cho form quản trị. Dùng closure thay vì hàm global vì
 * ProductWarehouseSpecTest.php đã khai báo hàm productPayload() toàn cục.
 */
function contentPayload(array $overrides = []): array
{
    return array_merge([
        'category_id' => test()->category->id,
        'code' => 'GET 10 CONTENT',
        'image' => '/userfiles/images/products/get.png',
        'name' => 'Vải địa kỹ thuật dệt 10 kN/m',
        'description' => 'Đoạn dẫn của sản phẩm.',
        'is_active' => 1,
    ], $overrides);
}

it('stores functions and applications text on create', function () {
    $functions = "## Ưu điểm\n\n- Cường độ cao\n- **Nhẹ**, dễ vận chuyển";
    $applicationsText = "## Bối cảnh\n\n- Dùng cho nền đường giao thông";

    $this->post('/admin/products', contentPayload([
        'functions' => $functions,
        'applications_text' => $applicationsText,
    ]))->assertRedirect('/admin/products');

    $product = Product::where('code', 'GET 10 CONTENT')->first();

    expect($product)->not->toBeNull()
        ->and($product->functions)->toBe($functions)
        ->and($product->applications_text)->toBe($applicationsText);
});

it('updates functions on edit', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'slug' => 'vai-kt-det-get-20-content',
        'code' => 'GET 20 CONTENT',
        'image' => '/userfiles/images/products/get.png',
        'name' => 'Vải địa kỹ thuật dệt 20 kN/m',
        'description' => 'Mô tả ban đầu',
        'functions' => 'Nội dung cũ',
    ]);

    $this->put("/admin/products/{$product->id}", contentPayload([
        'code' => 'GET 20 CONTENT',
        'functions' => "## Ưu điểm\n\n- Cập nhật thành công",
    ]))->assertRedirect('/admin/products');

    expect($product->fresh()->functions)->toBe("## Ưu điểm\n\n- Cập nhật thành công");
});

it('rejects a non string functions value', function () {
    $this->post('/admin/products', contentPayload([
        'functions' => ['không', 'phải', 'là', 'chuỗi'],
    ]))->assertSessionHasErrors('functions');
});

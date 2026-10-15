<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);

    $this->category = Category::create([
        'slug' => 'ro-da',
        'name' => 'Rọ đá',
        'sort_order' => 5,
        'is_active' => true,
    ]);
});

function productPayload(array $overrides = []): array
{
    return array_merge([
        'category_id' => test()->category->id,
        'warehouse' => Product::WAREHOUSE_FINISHED,
        'code' => 'RO-TG 1x1x1',
        'image' => '/userfiles/images/products/ro.png',
        'name' => 'Rọ tam giác',
        'description' => 'Mô tả rọ tam giác',
        'length_mm' => 1000,
        'width_mm' => 1000,
        'height_mm' => 1000,
        'mesh_size' => 'P8',
        'wire_diameter_mm' => 2.2,
        'is_active' => 1,
    ], $overrides);
}

it('stores warehouse and structured specs on create', function () {
    $this->post('/admin/products', productPayload())->assertRedirect('/admin/products');

    $product = Product::where('code', 'RO-TG 1x1x1')->first();

    expect($product)->not->toBeNull()
        ->and($product->warehouse)->toBe(Product::WAREHOUSE_FINISHED)
        ->and($product->length_mm)->toBe(1000)
        ->and($product->width_mm)->toBe(1000)
        ->and($product->height_mm)->toBe(1000)
        ->and($product->mesh_size)->toBe('P8')
        ->and($product->wire_diameter_mm)->toBe('2.2')
        ->and($product->dimensionsLabel())->toBe('1000 × 1000 × 1000 mm')
        ->and($product->warehouseLabel())->toBe('Kho thành phẩm');
});

it('defaults warehouse to merchandise when omitted', function () {
    $payload = productPayload(['code' => 'RO-MK 2x1x0.5']);
    unset($payload['warehouse']);

    $this->post('/admin/products', $payload)->assertRedirect('/admin/products');

    expect(Product::where('code', 'RO-MK 2x1x0.5')->first()->warehouse)
        ->toBe(Product::WAREHOUSE_MERCHANDISE);
});

it('rejects invalid warehouse, mesh size and wire diameter', function () {
    $this->post('/admin/products', productPayload(['warehouse' => 'kho-khong-ton-tai']))
        ->assertSessionHasErrors('warehouse');

    $this->post('/admin/products', productPayload(['mesh_size' => 'P99']))
        ->assertSessionHasErrors('mesh_size');

    $this->post('/admin/products', productPayload(['wire_diameter_mm' => 3]))
        ->assertSessionHasErrors('wire_diameter_mm');

    $this->post('/admin/products', productPayload(['length_mm' => -5]))
        ->assertSessionHasErrors('length_mm');
});

it('updates warehouse and specs on edit', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'warehouse' => Product::WAREHOUSE_MERCHANDISE,
        'slug' => 'ro-hinh-thang',
        'code' => 'RO-HT',
        'name' => 'Rọ hình thang',
        'description' => 'Mô tả',
        'image' => '/userfiles/images/products/ro.png',
    ]);

    $this->put('/admin/products/'.$product->id, productPayload([
        'code' => 'RO-HT',
        'warehouse' => Product::WAREHOUSE_FINISHED,
        'mesh_size' => 'P10',
        'wire_diameter_mm' => 2.7,
        'length_mm' => 2000,
        'width_mm' => 1000,
        'height_mm' => 500,
    ]))->assertRedirect('/admin/products');

    $product->refresh();

    expect($product->warehouse)->toBe(Product::WAREHOUSE_FINISHED)
        ->and($product->mesh_size)->toBe('P10')
        ->and($product->wire_diameter_mm)->toBe('2.7')
        ->and($product->dimensionsLabel())->toBe('2000 × 1000 × 500 mm');
});

it('filters admin index by warehouse', function () {
    $finished = Product::create([
        'category_id' => $this->category->id,
        'warehouse' => Product::WAREHOUSE_FINISHED,
        'slug' => 'ro-a',
        'code' => 'RO-A',
        'name' => 'Rọ A',
        'description' => 'a',
        'image' => '/x.png',
    ]);

    Product::create([
        'category_id' => $this->category->id,
        'warehouse' => Product::WAREHOUSE_MERCHANDISE,
        'slug' => 'ro-b',
        'code' => 'RO-B',
        'name' => 'Rọ B',
        'description' => 'b',
        'image' => '/x.png',
    ]);

    $this->get('/admin/products?warehouse=finished_goods')
        ->assertOk()
        ->assertSee('RO-A')
        ->assertDontSee('RO-B');

    $this->get('/admin/products?warehouse=merchandise')
        ->assertOk()
        ->assertSee('RO-B')
        ->assertDontSee('RO-A');

    $finished->delete();
});

it('sorts admin table by warehouse over ajax', function () {
    Product::create([
        'category_id' => $this->category->id,
        'warehouse' => Product::WAREHOUSE_MERCHANDISE,
        'slug' => 'ro-m1',
        'code' => 'RO-M1',
        'name' => 'Rọ M1',
        'description' => 'm',
        'image' => '/x.png',
    ]);

    Product::create([
        'category_id' => $this->category->id,
        'warehouse' => Product::WAREHOUSE_FINISHED,
        'slug' => 'ro-f1',
        'code' => 'RO-F1',
        'name' => 'Rọ F1',
        'description' => 'f',
        'image' => '/x.png',
    ]);

    $this->getJson('/admin/products?ajax=1&sort=warehouse&dir=asc')
        ->assertOk()
        ->assertJsonStructure(['recordsTotal', 'recordsFiltered', 'page', 'per_page', 'html'])
        ->assertJsonPath('recordsFiltered', 2);
});

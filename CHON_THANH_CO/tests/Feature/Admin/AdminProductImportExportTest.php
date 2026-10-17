<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('downloads the export file', function () {
    $category = Category::create(['slug' => 'cat-export', 'name' => 'Cat Export', 'sort_order' => 1, 'is_active' => true]);
    Product::create(['category_id' => $category->id, 'slug' => 'p-export', 'code' => 'EXPORT-1', 'name' => 'Export 1', 'description' => '', 'image' => '/x.png', 'is_active' => true]);

    $response = $this->get('/admin/products/export');

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('spreadsheet');
    expect($response->headers->get('content-disposition'))->toContain('san-pham-');
});

it('downloads the import template file', function () {
    $response = $this->get('/admin/products/template');

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('spreadsheet');
    expect($response->headers->get('content-disposition'))->toContain('mau-import-san-pham');
});

it('imports products from a csv file', function () {
    $category = Category::create(['slug' => 'vai-khong-det', 'name' => 'Vải không dệt', 'sort_order' => 1, 'is_active' => true]);

    $csv = "code,name,description,category,image,strength_min,strength_max,is_active,is_featured\n"
        ."IMPORT-01,Vải import 1,Mô tả 1,vai-khong-det,/x.png,20,30,1,1\n"
        ."IMPORT-02,Vải import 2,Mô tả 2,vai-khong-det,/x.png,,,0,0\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $this->post('/admin/products/import', ['file' => $file])
        ->assertRedirect('/admin/products')
        ->assertSessionHas('success');

    expect(Product::where('code', 'IMPORT-01')->exists())->toBeTrue();
    expect(Product::where('code', 'IMPORT-02')->exists())->toBeTrue();
    expect(Product::where('code', 'IMPORT-01')->first()->name)->toBe('Vải import 1');
    expect(Product::where('code', 'IMPORT-02')->first()->is_active)->toBeFalse();
});

it('rejects import without a file', function () {
    $this->post('/admin/products/import', [])->assertSessionHasErrors('file');
});

it('rejects import with an invalid file type', function () {
    $file = UploadedFile::fake()->create('products.txt', 100, 'text/plain');

    $this->post('/admin/products/import', ['file' => $file])->assertSessionHasErrors('file');
});
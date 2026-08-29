<?php

use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\ApplicationSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);
});

it('returns an empty images array when the product has no gallery', function () {
    getJson('/api/v1/products/luoi-dia-ky-thuat-geogrid')
        ->assertStatus(200)
        ->assertJsonPath('data.images', []);
});

it('returns gallery images in product detail with image and alt', function () {
    $product = Product::where('slug', 'luoi-dia-ky-thuat-geogrid')->firstOrFail();

    $product->images()->create([
        'image' => '/images/products/gabion-1.jpg',
        'alt' => 'Lưới địa kỹ thuật cuộn',
        'sort_order' => 1,
    ]);
    $product->images()->create([
        'image' => '/images/products/industrial-1.jpg',
        'alt' => 'Thi công lưới địa kỹ thuật',
        'sort_order' => 2,
    ]);

    getJson('/api/v1/products/luoi-dia-ky-thuat-geogrid')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data.images')
        ->assertJsonStructure(['data' => ['images' => ['*' => ['image', 'alt']]]])
        ->assertJsonPath('data.images.0.image', '/images/products/gabion-1.jpg')
        ->assertJsonPath('data.images.0.alt', 'Lưới địa kỹ thuật cuộn')
        ->assertJsonPath('data.images.1.image', '/images/products/industrial-1.jpg');
});

it('orders gallery images by sort_order', function () {
    $product = Product::where('slug', 'ro-da-gabion')->firstOrFail();

    // Tạo ảnh lộn xộn thứ tự để kiểm tra API trả đúng sort_order
    $product->images()->create(['image' => '/images/3.jpg', 'alt' => 'Ảnh 3', 'sort_order' => 3]);
    $product->images()->create(['image' => '/images/1.jpg', 'alt' => 'Ảnh 1', 'sort_order' => 1]);
    $product->images()->create(['image' => '/images/2.jpg', 'alt' => 'Ảnh 2', 'sort_order' => 2]);

    getJson('/api/v1/products/ro-da-gabion')
        ->assertStatus(200)
        ->assertJsonCount(3, 'data.images')
        ->assertJsonPath('data.images.0.image', '/images/1.jpg')
        ->assertJsonPath('data.images.1.image', '/images/2.jpg')
        ->assertJsonPath('data.images.2.image', '/images/3.jpg');
});

it('does not leak gallery images to other products', function () {
    $withImages = Product::where('slug', 'ro-da-gabion')->firstOrFail();
    $withoutImages = Product::where('slug', 'luoi-dia-ky-thuat-geogrid')->firstOrFail();

    $withImages->images()->create([
        'image' => '/images/products/gabion-1.jpg',
        'alt' => 'Rọ đá',
        'sort_order' => 1,
    ]);

    getJson('/api/v1/products/'.$withoutImages->slug)
        ->assertStatus(200)
        ->assertJsonPath('data.images', []);

    expect(ProductImage::where('product_id', $withImages->id)->count())->toBe(1);
});

it('keeps images out of the product list payload', function () {
    $product = Product::where('slug', 'ro-da-gabion')->firstOrFail();
    $product->images()->create([
        'image' => '/images/products/gabion-1.jpg',
        'alt' => 'Rọ đá',
        'sort_order' => 1,
    ]);

    $list = getJson('/api/v1/products?limit=3')->assertStatus(200)->json();

    collect($list['data'])->each(
        fn ($item) => expect($item)->not->toHaveKey('images')
    );
});

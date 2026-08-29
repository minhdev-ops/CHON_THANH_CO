<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

/**
 * Kiểm thử hệ thống với khối lượng dữ liệu lớn (volume).
 * - Keyset pagination phải duyệt đủ toàn bộ bản ghi, không trùng, không sót.
 * - Bộ lọc + phân loại vẫn đúng khi DB lớn.
 */

it('paginates through a large product catalog without gaps or duplicates', function () {
    $category = Category::create(['slug' => 'cat-volume', 'sort_order' => 1, 'is_active' => true]);

    // Tạo 1.000 sản phẩm bằng insertMany (nhanh hơn tạo từng dòng).
    $rows = [];
    for ($i = 1; $i <= 1000; $i++) {
        $rows[] = [
            'category_id' => $category->id,
            'slug' => "sp-volume-{$i}",
            'code' => "VOL-{$i}",
            'image' => '/x.png',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    Product::insert($rows);

    $cursor = null;
    $seen = [];
    $limit = 50;
    $pages = 0;

    do {
        $url = '/api/v1/products?limit='.$limit.($cursor ? '&cursor='.$cursor : '');
        $response = getJson($url)->assertStatus(200);

        $items = $response->json('data');
        foreach ($items as $item) {
            $seen[] = $item['slug'];
        }

        $cursor = $response->json('next_cursor');
        if (! empty($items)) {
            $pages++;
        }
    } while ($cursor !== null);

    expect($pages)->toBe(20); // 1000 / 50
    expect($seen)->toHaveCount(1000)
        ->and(count(array_unique($seen)))->toBe(1000); // không trùng, không sót
});

it('respects the requested limit and caps at 50', function () {
    $category = Category::create(['slug' => 'cat-limit', 'sort_order' => 1, 'is_active' => true]);

    $rows = [];
    for ($i = 1; $i <= 100; $i++) {
        $rows[] = [
            'category_id' => $category->id,
            'slug' => "sp-limit-{$i}",
            'code' => "LIM-{$i}",
            'image' => '/x.png',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    Product::insert($rows);

    getJson('/api/v1/products?limit=10')->assertJsonCount(10, 'data');

    // limit lớn hơn 50 bị giới hạn về 50
    getJson('/api/v1/products?limit=500')->assertJsonCount(50, 'data');
});

it('still filters correctly across a large catalog', function () {
    $catA = Category::create(['slug' => 'cat-a', 'sort_order' => 1, 'is_active' => true]);
    $catB = Category::create(['slug' => 'cat-b', 'sort_order' => 1, 'is_active' => true]);

    // 30 sản phẩm thuộc cat-a, 100 thuộc cat-b
    $rows = [];
    for ($i = 1; $i <= 130; $i++) {
        $rows[] = [
            'category_id' => $i <= 30 ? $catA->id : $catB->id,
            'slug' => 'sp-f'.$i,
            'code' => "FLT-{$i}",
            'image' => '/x.png',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    Product::insert($rows);

    // limit mặc định là 12 (controller giới hạn), xin limit 50 để lấy đủ 30
    $response = getJson('/api/v1/products?category=cat-a&limit=50')
        ->assertStatus(200)
        ->assertJsonCount(30, 'data');

    $body = $response->json('data');
    foreach ($body as $item) {
        expect($item['category']['slug'] ?? null)->toBe('cat-a');
    }
    expect($response->json('next_cursor'))->toBeNull(); // 30 < limit 50
});
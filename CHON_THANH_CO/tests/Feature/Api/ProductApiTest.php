<?php

use Database\Seeders\ApplicationSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns active products with keyset pagination', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/products')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'slug',
                    'code',
                    'name',
                    'image',
                    'strength_min',
                    'strength_max',
                    'category' => ['slug', 'name'],
                ],
            ],
            'next_cursor',
        ]);
});

it('supports keyset cursor pagination for products', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    $first = getJson('/api/v1/products?limit=2')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->json();

    expect($first['next_cursor'])->not->toBeNull();

    $second = getJson('/api/v1/products?limit=2&cursor='.$first['next_cursor'])
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->json();

    $firstSlugs = collect($first['data'])->pluck('slug');
    $secondSlugs = collect($second['data'])->pluck('slug');

    expect($firstSlugs->intersect($secondSlugs))->toBeEmpty();
});

it('filters products by multiple categories', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/products?category=vai-kt-khong-det,vai-kt-det&limit=50')
        ->assertStatus(200)
        ->assertJsonCount(27, 'data');
});

it('returns full product detail with description, specs, applications and category', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/products/vai-kt-khong-det-art-30')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'slug',
                'code',
                'name',
                'image',
                'strength_label',
                'strength_min',
                'strength_max',
                'category' => ['slug', 'name'],
                'applications' => ['*' => ['slug', 'name']],
                'description',
                'specs' => ['*' => ['icon', 'label', 'value']],
            ],
        ])
        ->assertJsonPath('data.slug', 'vai-kt-khong-det-art-30')
        ->assertJsonPath('data.code', 'ART 30')
        ->assertJsonPath('data.strength_label', '30 kN/m')
        ->assertJsonPath('data.category.slug', 'vai-kt-khong-det')
        ->assertJsonCount(4, 'data.specs')
        ->assertJsonPath('data.specs.0.label', 'Khổ rộng')
        ->assertJsonCount(3, 'data.applications');
});

it('can fetch product details by slug', function () {
    getJson('/api/v1/products/non-existent-slug')
        ->assertStatus(404);
});

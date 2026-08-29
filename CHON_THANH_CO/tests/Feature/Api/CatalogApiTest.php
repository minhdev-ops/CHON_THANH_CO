<?php

use App\Models\Category;
use Database\Seeders\ApplicationSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns active categories with product counts', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/categories')
        ->assertStatus(200)
        ->assertJsonCount(8, 'data')
        ->assertJsonStructure(['data' => ['*' => ['slug', 'name', 'description', 'products_count']]])
        ->assertJsonPath('data.0.slug', 'vai-kt-khong-det')
        ->assertJsonPath('data.0.name', 'Vải địa kỹ thuật không dệt')
        ->assertJsonPath('data.0.products_count', 15);
});

it('serves categories in english', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/categories', ['X-Locale' => 'en'])
        ->assertStatus(200)
        ->assertJsonPath('data.0.name', 'Non-woven Geotextile');
});

it('excludes inactive categories', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    Category::where('slug', 'ro-da')->update(['is_active' => false]);

    $slugs = collect(getJson('/api/v1/categories')->assertStatus(200)->json('data'))
        ->pluck('slug');

    expect($slugs)->toHaveCount(7)
        ->not->toContain('ro-da');
});

it('returns active applications with product counts', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/applications')
        ->assertStatus(200)
        ->assertJsonCount(7, 'data')
        ->assertJsonStructure(['data' => ['*' => ['slug', 'name', 'description', 'products_count']]])
        ->assertJsonPath('data.0.slug', 'phan-cach-loc')
        ->assertJsonPath('data.0.name', 'Phân cách, lọc');
});

it('serves applications in english', function () {
    seed([CategorySeeder::class, ApplicationSeeder::class, ProductSeeder::class]);

    getJson('/api/v1/applications', ['X-Locale' => 'en'])
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['*' => ['slug', 'name']]])
        ->assertJsonPath('data.0.name', 'Separation & Filtration');
});

<?php

use App\Models\Product;
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

it('exposes warehouse and spec fields on the product list', function () {
    getJson('/api/v1/products?limit=1')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'warehouse',
                    'length_mm',
                    'width_mm',
                    'height_mm',
                    'mesh_size',
                    'wire_diameter_mm',
                ],
            ],
        ]);
});

it('filters products by warehouse', function () {
    $response = getJson('/api/v1/products?warehouse=finished_goods&limit=50')
        ->assertOk();

    $warehouses = collect($response->json('data'))->pluck('warehouse')->unique();

    expect($warehouses->all())->toBe(['finished_goods']);
});

it('returns gabion spec values for finished goods', function () {
    getJson('/api/v1/products/ro-tam-giac')
        ->assertOk()
        ->assertJsonPath('data.warehouse', Product::WAREHOUSE_FINISHED)
        ->assertJsonPath('data.length_mm', 1000)
        ->assertJsonPath('data.width_mm', 1000)
        ->assertJsonPath('data.height_mm', 1000)
        ->assertJsonPath('data.mesh_size', 'P8')
        ->assertJsonPath('data.wire_diameter_mm', '2.2');
});

it('seeds every product into a warehouse group', function () {
    expect(Product::whereNull('warehouse')->count())->toBe(0);

    expect(Product::where('warehouse', Product::WAREHOUSE_FINISHED)->count())
        ->toBe(4)
        ->and(Product::where('warehouse', Product::WAREHOUSE_MERCHANDISE)->count())
        ->toBeGreaterThan(20);
});

it('ignores an unknown warehouse filter', function () {
    $all = getJson('/api/v1/products?limit=50')->assertOk()->json('data');

    getJson('/api/v1/products?warehouse=khong-ton-tai&limit=50')
        ->assertOk()
        ->assertJsonCount(count($all), 'data');
});

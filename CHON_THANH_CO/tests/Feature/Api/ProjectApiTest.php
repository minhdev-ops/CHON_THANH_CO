<?php

use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns active projects with keyset pagination', function () {
    seed(ProjectSeeder::class);

    getJson('/api/v1/projects')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'slug',
                    'name',
                    'location',
                    'period',
                    'hero_image',
                    'desc_image',
                ],
            ],
            'next_cursor',
        ]);
});

it('supports keyset cursor pagination for projects', function () {
    seed(ProjectSeeder::class);

    $first = getJson('/api/v1/projects?limit=2')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->json();

    expect($first['next_cursor'])->not->toBeNull();

    $second = getJson('/api/v1/projects?limit=2&cursor='.$first['next_cursor'])
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->json();

    $firstSlugs = collect($first['data'])->pluck('slug');
    $secondSlugs = collect($second['data'])->pluck('slug');

    expect($firstSlugs->intersect($secondSlugs))->toBeEmpty();
});

it('returns full project detail with description, materials and gallery', function () {
    seed(ProjectSeeder::class);

    getJson('/api/v1/projects/cao-toc-bac-bac-quang-nam')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'slug',
                'name',
                'location',
                'period',
                'area',
                'hero_image',
                'desc_image',
                'description',
                'materials' => ['*' => ['name', 'detail', 'image']],
                'gallery' => ['*' => ['image', 'alt']],
            ],
        ])
        ->assertJsonPath('data.slug', 'cao-toc-bac-bac-quang-nam')
        ->assertJsonPath('data.name', 'Đường cao tốc Bắc Bắc — Quảng Nam')
        ->assertJsonPath('data.period', '2024 - 2025')
        ->assertJsonPath('data.area', '50,000 m²')
        ->assertJsonCount(2, 'data.materials')
        ->assertJsonPath('data.materials.0.name', 'Lưới địa kỹ thuật')
        ->assertJsonCount(6, 'data.gallery');
});

it('keeps project detail fields out of the list payload', function () {
    seed(ProjectSeeder::class);

    $list = getJson('/api/v1/projects?limit=3')->assertStatus(200)->json();

    collect($list['data'])->each(
        fn ($item) => expect($item)->not->toHaveKey('description')
            ->not->toHaveKey('materials')
            ->not->toHaveKey('gallery')
    );
});

it('can fetch project details by slug', function () {
    getJson('/api/v1/projects/non-existent-project')
        ->assertStatus(404);
});

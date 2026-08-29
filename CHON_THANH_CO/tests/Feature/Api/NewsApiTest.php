<?php

use Database\Seeders\NewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns active news articles with keyset pagination', function () {
    seed(NewsSeeder::class);

    getJson('/api/v1/news')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'slug',
                    'title',
                    'excerpt',
                    'image',
                    'published_at',
                ],
            ],
            'next_cursor',
        ]);
});

it('supports keyset cursor pagination for news', function () {
    seed(NewsSeeder::class);

    $first = getJson('/api/v1/news?limit=2')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->json();

    expect($first['next_cursor'])->not->toBeNull();

    $second = getJson('/api/v1/news?limit=2&cursor='.$first['next_cursor'])
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->json();

    $firstSlugs = collect($first['data'])->pluck('slug');
    $secondSlugs = collect($second['data'])->pluck('slug');

    expect($firstSlugs->intersect($secondSlugs))->toBeEmpty();
});

it('returns news categories', function () {
    seed(NewsSeeder::class);

    getJson('/api/v1/news/categories')
        ->assertStatus(200)
        ->assertJsonStructure(['*' => ['slug', 'name']]);
});

it('returns a single news article by slug with content', function () {
    seed(NewsSeeder::class);

    getJson('/api/v1/news/giai-phap-gia-co-nen-dat-yeu')
        ->assertStatus(200)
        ->assertJsonPath('data.slug', 'giai-phap-gia-co-nen-dat-yeu')
        ->assertJsonStructure([
            'data' => ['slug', 'title', 'excerpt', 'image', 'published_at', 'content'],
        ]);
});

it('returns 404 for an unknown news slug', function () {
    seed(NewsSeeder::class);

    getJson('/api/v1/news/khong-ton-tai')
        ->assertStatus(404);
});

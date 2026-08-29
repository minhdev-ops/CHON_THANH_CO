<?php

use App\Models\Certificate;
use Database\Seeders\CertificateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns active certificates with the correct structure', function () {
    seed(CertificateSeeder::class);

    getJson('/api/v1/certificates')
        ->assertStatus(200)
        ->assertJsonCount(4, 'data')
        ->assertJsonStructure(['data' => ['*' => ['slug', 'name', 'description', 'image', 'file']]])
        ->assertJsonPath('data.0.slug', 'giay-uy-quyen-hock')
        ->assertJsonPath('data.0.name', 'Giấy uỷ quyền phân phối HOCK')
        ->assertJsonPath('data.0.image', '/images/certificates/authorization-hock.jpg');
});

it('serves certificates in english', function () {
    seed(CertificateSeeder::class);

    getJson('/api/v1/certificates', ['X-Locale' => 'en'])
        ->assertStatus(200)
        ->assertJsonPath('data.0.name', 'HOCK Authorized Distributor Letter');
});

it('excludes inactive certificates', function () {
    seed(CertificateSeeder::class);

    Certificate::where('slug', 'iso-9001-2015')->update(['is_active' => false]);

    $slugs = collect(getJson('/api/v1/certificates')->assertStatus(200)->json('data'))
        ->pluck('slug');

    expect($slugs)->toHaveCount(3)
        ->not->toContain('iso-9001-2015')
        ->toContain('giay-uy-quyen-hock');
});

it('returns an empty list when there are no certificates', function () {
    getJson('/api/v1/certificates')
        ->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

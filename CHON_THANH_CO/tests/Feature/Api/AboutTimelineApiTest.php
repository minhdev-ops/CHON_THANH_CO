<?php

use App\Models\AboutTimeline;
use Database\Seeders\AboutTimelineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns active timeline milestones ordered by sort_order', function () {
    seed(AboutTimelineSeeder::class);

    $response = getJson('/api/v1/about/timeline')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['year', 'description'],
            ],
        ]);

    // 6 mốc từ seeder, sắp xếp theo sort_order
    $response->assertJsonCount(6, 'data');

    $years = collect($response->json('data'))->pluck('year');
    expect($years->first())->toBe('2005 - Thành lập')
        ->and($years->last())->toBe('Hiện tại');

    $response->assertJsonPath('data.0.description', 'Thành lập với tên gọi CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837 do Sở KH&ĐT TP.HCM cấp.');
});

it('serves english translations when requesting the en locale', function () {
    seed(AboutTimelineSeeder::class);

    getJson('/api/v1/about/timeline', ['X-Locale' => 'en'])
        ->assertStatus(200)
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.0.year', '2005 - Founding')
        ->assertJsonPath('data.5.year', 'Today');
});

it('supports locale via query parameter', function () {
    seed(AboutTimelineSeeder::class);

    getJson('/api/v1/about/timeline?locale=en')
        ->assertStatus(200)
        ->assertJsonPath('data.5.year', 'Today');
});

it('excludes inactive milestones from the timeline', function () {
    seed(AboutTimelineSeeder::class);

    $inactive = AboutTimeline::orderBy('sort_order')->first();
    $inactive->update(['is_active' => false]);

    getJson('/api/v1/about/timeline')
        ->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('data.0.year', '2007 - Nhà máy Rọ đá Á Châu');
});

it('falls back to the vi translation when the requested locale is missing', function () {
    seed(AboutTimelineSeeder::class);

    // Thêm một mốc chỉ có bản dịch tiếng Việt
    $item = AboutTimeline::create(['sort_order' => 99, 'is_active' => true]);
    $item->translations()->create([
        'locale' => 'vi',
        'year' => '2026 - Mốc mới',
        'description' => 'Chỉ có bản tiếng Việt.',
    ]);

    getJson('/api/v1/about/timeline?locale=en')
        ->assertStatus(200)
        ->assertJsonCount(7, 'data')
        ->assertJsonPath('data.6.year', '2026 - Mốc mới')
        ->assertJsonPath('data.6.description', 'Chỉ có bản tiếng Việt.');
});

it('returns an empty list when there are no milestones', function () {
    getJson('/api/v1/about/timeline')
        ->assertStatus(200)
        ->assertJsonPath('data', []);
});

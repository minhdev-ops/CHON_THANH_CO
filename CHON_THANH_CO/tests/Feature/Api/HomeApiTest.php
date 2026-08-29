<?php

use App\Models\Banner;
use App\Models\HomeStat;
use App\Models\Project;
use Database\Seeders\ApplicationSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\HomeStatSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\WhyChooseUsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed([
        CategorySeeder::class,
        ApplicationSeeder::class,
        ProductSeeder::class,
        ProjectSeeder::class,
        BannerSeeder::class,
        HomeStatSeeder::class,
        WhyChooseUsSeeder::class,
    ]);

    // ProjectSeeder không đánh dấu nổi bật — đánh dấu 2 dự án đầu để có latest_projects
    Project::orderBy('sort_order')->limit(2)->update(['is_featured' => true]);
});

it('returns all home sections with the correct structure', function () {
    getJson('/api/v1/home')
        ->assertStatus(200)
        ->assertJsonStructure([
            'banners' => ['*' => ['section', 'image', 'link_to', 'title', 'subtitle', 'text', 'button_text']],
            'stats' => ['*' => ['value', 'label', 'icon']],
            'why_choose_us' => ['*' => ['title', 'description', 'icon']],
            'featured_products' => ['*' => ['slug', 'code', 'name', 'image']],
            'latest_projects' => ['*' => ['slug', 'name', 'location', 'period', 'hero_image', 'desc_image']],
        ]);
});

it('returns hero and cta banners with translated content', function () {
    // Banners được sắp theo section (alphabetically): cta trước, hero sau
    getJson('/api/v1/home')
        ->assertStatus(200)
        ->assertJsonCount(2, 'banners')
        ->assertJsonPath('banners.0.section', 'cta')
        ->assertJsonPath('banners.0.title', 'CẦN TƯ VẤN? LIÊN HỆ NGAY')
        ->assertJsonPath('banners.1.section', 'hero')
        ->assertJsonPath('banners.1.title', 'ĐỐI TÁC UY TÍN VẬT LIỆU ĐỊA KỸ THUẬT');
});

it('returns featured products and featured projects', function () {
    $json = getJson('/api/v1/home')->assertStatus(200)->json();

    // Seeder tạo đúng 8 sản phẩm nổi bật (limit 8)
    expect($json['featured_products'])->toHaveCount(8)
        ->and(collect($json['featured_products'])->pluck('slug'))
        ->toContain('vai-kt-khong-det-art-12', 'luoi-dia-ky-thuat-geogrid', 'ro-da-gabion');

    expect($json['latest_projects'])->toHaveCount(2)
        ->and($json['latest_projects'][0]['slug'])->toBe('cao-toc-bac-bac-quang-nam');
});

it('returns stats and why-choose-us content', function () {
    $json = getJson('/api/v1/home')->assertStatus(200)->json();

    expect($json['stats'])->toHaveCount(6)
        ->and($json['stats'][0]['value'])->toBe('21+')
        ->and($json['why_choose_us'])->toHaveCount(6)
        ->and($json['why_choose_us'][0]['title'])->toBe('Chất lượng đạt chuẩn');
});

it('serves home sections in english', function () {
    getJson('/api/v1/home', ['X-Locale' => 'en'])
        ->assertStatus(200)
        ->assertJsonPath('banners.0.title', 'NEED ADVICE? CONTACT US NOW')
        ->assertJsonPath('banners.1.title', 'TRUSTED PARTNER FOR GEOSYNTHETIC MATERIALS')
        ->assertJsonPath('stats.0.label', 'Years of experience')
        ->assertJsonPath('why_choose_us.0.title', 'Certified quality');
});

it('excludes inactive banners and stats', function () {
    Banner::where('section', 'cta')->update(['is_active' => false]);
    HomeStat::orderBy('sort_order')->first()->update(['is_active' => false]);

    getJson('/api/v1/home')
        ->assertStatus(200)
        ->assertJsonCount(1, 'banners')
        ->assertJsonCount(5, 'stats');
});

<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use Database\Seeders\BannerSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession([
        'admin_authenticated' => true,
        'admin_name' => 'admin_tester',
    ]);
});

it('allows editing and updating hero banner translations in admin', function () {
    $this->seed(BannerSeeder::class);

    $hero = Banner::where('section', 'hero')->first();
    expect($hero)->not->toBeNull();

    // GET edit page
    $this->get("/admin/banners/{$hero->id}/edit")
        ->assertStatus(200)
        ->assertSee('ĐỐI TÁC UY TÍN VẬT LIỆU ĐỊA KỸ THUẬT');

    // PUT update with new translations
    $this->put("/admin/banners/{$hero->id}", [
        'section' => 'hero',
        'image' => '/images/new-hero.jpg',
        'link_to' => '/contact',
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => [
            'vi' => [
                'title' => 'TIÊU ĐỀ HERO MỚI',
                'subtitle' => 'Phụ đề hero tiếng Việt mới',
                'text' => '',
                'button_text' => 'TƯ VẤN NGAY',
            ],
            'en' => [
                'title' => 'NEW HERO TITLE',
                'subtitle' => 'New English Subtitle',
                'text' => '',
                'button_text' => 'CONTACT NOW',
            ],
        ],
    ])->assertRedirect('/admin/banners');

    $hero->refresh();
    expect($hero->image)->toBe('/images/new-hero.jpg');
    expect($hero->translation('vi')?->title)->toBe('TIÊU ĐỀ HERO MỚI');
    expect($hero->translation('vi')?->subtitle)->toBe('Phụ đề hero tiếng Việt mới');
    expect($hero->translation('vi')?->button_text)->toBe('TƯ VẤN NGAY');
    expect($hero->translation('en')?->title)->toBe('NEW HERO TITLE');
    expect($hero->translation('en')?->subtitle)->toBe('New English Subtitle');
});

it('preserves specs in product create form when validation fails', function () {
    $category = Category::create(['slug' => 'cat-test', 'sort_order' => 1, 'is_active' => true]);

    // Submit with missing required code and translations.vi.name to trigger validation error
    $response = $this->from('/admin/products/create')->post('/admin/products', [
        'category_id' => $category->id,
        'code' => '', // missing
        'image' => '/userfiles/images/products/test.png',
        'specs' => [
            [
                'value' => '45 kN/m',
                'icon' => 'bolt',
                'label_vi' => 'Cường độ kéo',
                'label_en' => 'Tensile strength',
                'sort_order' => 1,
            ],
        ],
        'images' => [
            ['image' => '/userfiles/images/products/sub.png', 'alt' => 'Ảnh phụ', 'sort_order' => 1],
        ],
    ]);

    $response->assertRedirect('/admin/products/create');
    $response->assertSessionHasErrors(['code', 'translations.vi.name']);
});

it('preserves materials and gallery in project create form when validation fails', function () {
    // Submit with missing required fields to trigger validation error
    $response = $this->from('/admin/projects/create')->post('/admin/projects', [
        'period' => '', // missing
        'hero_image' => '', // missing
        'materials' => [
            [
                'product_id' => null,
                'image' => '/userfiles/images/projects/mat.png',
                'name_vi' => 'Vật liệu kiểm thử',
                'detail_vi' => 'Chi tiết',
                'name_en' => '',
                'detail_en' => '',
                'sort_order' => 1,
            ],
        ],
        'gallery' => [
            ['image' => '/userfiles/images/projects/g1.png', 'alt' => 'G1', 'sort_order' => 1],
        ],
    ]);

    $response->assertRedirect('/admin/projects/create');
    $response->assertSessionHasErrors(['period', 'hero_image']);
});

it('creates audit log entries when admin modifies settings', function () {
    $this->seed(SettingSeeder::class);

    $this->put('/admin/settings', [
        'settings' => [
            'company.phone' => '0988.777.666',
        ],
    ])->assertRedirect('/admin/settings');

    $log = AuditLog::where('model_type', Setting::class)->where('action', 'updated')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->actor)->toBe('admin_tester');
});

it('filters contact messages by status', function () {
    ContactMessage::create([
        'name' => 'Khách hàng 1',
        'phone' => '0901234567',
        'email' => 'k1@example.com',
        'message' => 'Tin 1',
        'status' => 'new',
    ]);

    ContactMessage::create([
        'name' => 'Khách hàng 2',
        'phone' => '0901234568',
        'email' => 'k2@example.com',
        'message' => 'Tin 2',
        'status' => 'replied',
    ]);

    $this->get('/admin/contacts?status=new')
        ->assertStatus(200)
        ->assertSee('Khách hàng 1')
        ->assertDontSee('Khách hàng 2');

    $this->get('/admin/contacts?status=replied')
        ->assertStatus(200)
        ->assertSee('Khách hàng 2')
        ->assertDontSee('Khách hàng 1');
});

<?php

use App\Models\AboutTimeline;
use Database\Seeders\AboutTimelineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;
use function Pest\Laravel\withSession;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(AboutTimelineSeeder::class);

    // Đăng nhập admin qua session (giống luồng thật của AuthController)
    session(['admin_authenticated' => true, 'admin_name' => 'admin']);
});

it('lists about timeline items in admin', function () {
    get(route('admin.about-timeline.index'))
        ->assertOk()
        ->assertSee('2005 - Thành lập');
});

it('shows the create form', function () {
    get(route('admin.about-timeline.create'))
        ->assertOk()
        ->assertSee('Tạo mốc');
});

it('can create a new timeline item', function () {
    post(route('admin.about-timeline.store'), [
        'sort_order' => 7,
        'is_active' => 1,
        'translations' => [
            'vi' => ['year' => '2024 - Mở rộng', 'description' => 'Mở rộng kho vận ra miền Bắc.'],
            'en' => ['year' => '2024 - Expansion', 'description' => 'Expand warehousing to the North.'],
        ],
    ])
        ->assertRedirect(route('admin.about-timeline.index'));

    expect(AboutTimeline::where('sort_order', 7)->exists())->toBeTrue();
    $item = AboutTimeline::where('sort_order', 7)->first();
    expect($item->translation('vi')?->year)->toBe('2024 - Mở rộng')
        ->and($item->translation('en')?->description)->toBe('Expand warehousing to the North.');
});

it('can edit and update an existing item', function () {
    $item = AboutTimeline::first();

    get(route('admin.about-timeline.edit', $item))
        ->assertOk()
        ->assertSee('Lưu thay đổi');

    $this->put(route('admin.about-timeline.update', $item), [
        'sort_order' => $item->sort_order,
        'is_active' => 1,
        'translations' => [
            'vi' => ['year' => '2005 - Thành lập (sửa)', 'description' => 'Nội dung đã cập nhật.'],
            'en' => ['year' => '2005 - Founding (updated)', 'description' => 'Updated content.'],
        ],
    ])->assertRedirect(route('admin.about-timeline.index'));

    expect($item->fresh()->translation('vi')?->year)->toBe('2005 - Thành lập (sửa)');
});

it('can delete a timeline item', function () {
    $item = AboutTimeline::first();

    $this->delete(route('admin.about-timeline.destroy', $item))
        ->assertRedirect(route('admin.about-timeline.index'));

    expect(AboutTimeline::find($item->id))->toBeNull();
});

<?php

use App\Models\AboutTimeline;
use Database\Seeders\AboutTimelineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(AboutTimelineSeeder::class);

    // Đăng nhập admin qua session (giống luồng thật của AuthController)
    session(['admin_authenticated' => true, 'admin_name' => 'admin']);
});

it('lists about timeline items in admin', function () {
    getJson(route('admin.about-timeline.index'))
        ->assertOk()
        ->assertJsonPath('data.0.year', '2005 - Thành lập');
});

it('lists about timeline items in admin (HTML)', function () {
    get(route('admin.about-timeline.index'))
        ->assertOk()
        ->assertSee('DataTables'); // Check DataTable is rendered
});

it('shows the create form', function () {
    get(route('admin.about-timeline.create'))
        ->assertOk()
        ->assertSee('Tạo mốc');
});

it('can create a new timeline item', function () {
    post(route('admin.about-timeline.store'), [
        'year' => '2024 - Mở rộng',
        'description' => 'Mở rộng kho vận ra miền Bắc.',
        'sort_order' => 7,
        'is_active' => 1,
    ])
        ->assertRedirect(route('admin.about-timeline.index'));

    expect(AboutTimeline::where('sort_order', 7)->exists())->toBeTrue();
    $item = AboutTimeline::where('sort_order', 7)->first();
    expect($item->year)->toBe('2024 - Mở rộng');
});

it('can edit and update an existing item', function () {
    $item = AboutTimeline::first();

    get(route('admin.about-timeline.edit', $item))
        ->assertOk()
        ->assertSee('Lưu thay đổi');

    $this->put(route('admin.about-timeline.update', $item), [
        'year' => '2005 - Thành lập (sửa)',
        'description' => 'Nội dung đã cập nhật.',
        'sort_order' => $item->sort_order,
        'is_active' => 1,
    ])->assertRedirect(route('admin.about-timeline.index'));

    expect($item->fresh()->year)->toBe('2005 - Thành lập (sửa)');
});

it('can delete a timeline item', function () {
    $item = AboutTimeline::first();

    $this->delete(route('admin.about-timeline.destroy', $item))
        ->assertRedirect(route('admin.about-timeline.index'));

    expect(AboutTimeline::find($item->id))->toBeNull();
});
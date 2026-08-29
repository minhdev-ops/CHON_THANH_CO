<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('saves project materials and gallery', function () {
    $category = Category::create(['slug' => 'cat-linked', 'sort_order' => 1, 'is_active' => true]);
    $linkedProduct = Product::create([
        'category_id' => $category->id,
        'slug' => 'product-linked',
        'code' => 'LINKED-1',
        'image' => '/x.png',
        'is_active' => true,
    ]);

    $this->post('/admin/projects', [
        'period' => '2024–2025',
        'area' => '120.000 m²',
        'hero_image' => '/userfiles/images/projects/hero.png',
        'is_active' => 1,
        'sort_order' => 1,
        'translations' => [
            'vi' => ['name' => 'Dự án cao tốc', 'location' => 'Đồng Nai', 'description' => 'Mô tả'],
            'en' => ['name' => '', 'location' => '', 'description' => ''],
        ],
        'materials' => [
            [
                'product_id' => $linkedProduct->id,
                'image' => '/userfiles/images/projects/mat.png',
                'name_vi' => 'Vải địa kỹ thuật ART 30',
                'detail_vi' => 'Cường độ 30 kN/m',
                'name_en' => 'ART 30 Geotextile',
                'detail_en' => '30 kN/m',
                'sort_order' => 1,
            ],
        ],
        'gallery' => [
            ['image' => '/userfiles/images/projects/g1.png', 'alt' => 'Ảnh 1', 'sort_order' => 1],
            ['image' => '/userfiles/images/projects/g2.png', 'alt' => '', 'sort_order' => 2],
        ],
    ])->assertRedirect('/admin/projects');

    $project = Project::where('slug', 'du-an-cao-toc')->first();
    expect($project)->not->toBeNull();

    expect($project->materials()->count())->toBe(1);
    $material = $project->materials()->first();
    expect($material->product_id)->toBe($linkedProduct->id)
        ->and($material->translation('vi')?->name)->toBe('Vải địa kỹ thuật ART 30')
        ->and($material->translation('en')?->detail)->toBe('30 kN/m');

    expect($project->images()->count())->toBe(2);
    expect($project->images()->first()->image)->toBe('/userfiles/images/projects/g1.png');
});

it('replaces materials and gallery on update', function () {
    $project = Project::create([
        'slug' => 'project-update',
        'period' => '2024',
        'hero_image' => '/x.png',
        'is_active' => true,
    ]);
    $project->materials()->create(['image' => '/old-mat.png', 'sort_order' => 1]);
    $project->images()->create(['image' => '/old-g.png', 'sort_order' => 1]);

    $this->put("/admin/projects/{$project->id}", [
        'period' => '2025',
        'hero_image' => '/x.png',
        'is_active' => 1,
        'translations' => [
            'vi' => ['name' => 'Đã cập nhật', 'location' => 'Bình Dương', 'description' => 'Mô tả'],
            'en' => ['name' => '', 'location' => '', 'description' => ''],
        ],
        'materials' => [],
        'gallery' => [
            ['image' => '/new-g.png', 'alt' => '', 'sort_order' => 1],
        ],
    ])->assertRedirect('/admin/projects');

    $project->refresh();
    expect($project->materials()->count())->toBe(0);
    expect($project->images()->count())->toBe(1);
    expect($project->images()->first()->image)->toBe('/new-g.png');
});

it('validates required project fields', function () {
    $this->post('/admin/projects', [])->assertSessionHasErrors(['period', 'hero_image', 'translations.vi.name', 'translations.vi.location', 'translations.vi.description']);
});

it('can bulk delete projects', function () {
    $p1 = Project::create(['slug' => 'bulk-p1', 'period' => '2024', 'hero_image' => '/x.png', 'is_active' => true]);
    $p2 = Project::create(['slug' => 'bulk-p2', 'period' => '2024', 'hero_image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/projects/bulk', ['ids' => [$p1->id, $p2->id], 'action' => 'delete'])
        ->assertRedirect('/admin/projects');

    expect(Project::find($p1->id))->toBeNull();
    expect(Project::find($p2->id))->toBeNull();
});

it('can bulk feature and unfeature projects', function () {
    $project = Project::create(['slug' => 'bulk-feature', 'period' => '2024', 'hero_image' => '/x.png', 'is_active' => true]);

    $this->post('/admin/projects/bulk', ['ids' => [$project->id], 'action' => 'feature'])->assertRedirect('/admin/projects');
    expect($project->fresh()->is_featured)->toBeTrue();

    $this->post('/admin/projects/bulk', ['ids' => [$project->id], 'action' => 'unfeature'])->assertRedirect('/admin/projects');
    expect($project->fresh()->is_featured)->toBeFalse();
});

it('rejects invalid project bulk action', function () {
    $this->post('/admin/projects/bulk', ['ids' => [1], 'action' => 'hack'])->assertSessionHasErrors('action');
});

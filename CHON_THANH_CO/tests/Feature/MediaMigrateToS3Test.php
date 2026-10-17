<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Storage::fake không kế thừa config url của disk — truyền thẳng url vào fake.
    Storage::fake('s3', ['url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

function makeLocalFixture(string $relativePath, string $content): string
{
    $full = public_path(ltrim($relativePath, '/'));
    if (! is_dir(dirname($full))) {
        mkdir(dirname($full), 0755, true);
    }
    file_put_contents($full, $content);

    return $full;
}

it('uploads local media to s3 with the documented key mapping', function () {
    // Dùng tên file riêng (không đụng logo/PDF thật của site) để cleanup an toàn.
    $files = [
        makeLocalFixture('userfiles/images/products/sp.jpg', 'img') => 'images/products/sp.jpg',
        makeLocalFixture('userfiles/files/documents/spec.pdf', 'pdf') => 'files/documents/spec.pdf',
        makeLocalFixture('images/migrate-fixture-logo.svg', 'svg') => 'site-images/migrate-fixture-logo.svg',
        makeLocalFixture('documents/migrate-fixture.pdf', 'pdf') => 'documents/migrate-fixture.pdf',
    ];

    $this->artisan('media:migrate-to-s3')->assertExitCode(0);

    foreach ($files as $key) {
        Storage::disk('s3')->assertExists($key);
    }

    array_map('unlink', array_keys($files));
});

it('rewrites db paths to s3 keys', function () {
    // Schema thật (migration 2026_09_26): categories.name, products.description,
    // news.title/excerpt là NOT NULL; bảng news_translations đã bị gỡ.
    DB::table('categories')->insert([
        'slug' => 'danh-muc-test', 'name' => 'Danh mục test',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('products')->insert([
        'category_id' => DB::table('categories')->max('id'),
        'slug' => 'sp-1', 'code' => 'SP1', 'name' => 'Sản phẩm 1',
        'description' => 'Mô tả sản phẩm',
        'image' => '/userfiles/images/products/sp.jpg',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('banners')->insert([
        'title' => 'B', 'image' => '/userfiles/images/banners/b.jpg',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('news')->insert([
        'slug' => 'tin-1', 'title' => 'Tin 1', 'excerpt' => 'Trích đoạn',
        'image' => '/userfiles/images/news/n.jpg',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('settings')->insert([
        ['key' => 'company.capability_file', 'value' => '/documents/ho-so-nang-luc-2026.pdf', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
    ]);

    makeLocalFixture('userfiles/images/products/sp.jpg', 'img');

    $this->artisan('media:migrate-to-s3')->assertExitCode(0);

    expect(DB::table('products')->value('image'))->toBe('images/products/sp.jpg')
        ->and(DB::table('banners')->value('image'))->toBe('images/banners/b.jpg')
        ->and(DB::table('news')->value('image'))->toBe('images/news/n.jpg')
        ->and(DB::table('settings')->where('key', 'company.capability_file')->value('value'))
        ->toBe('documents/ho-so-nang-luc-2026.pdf');

    unlink(public_path('userfiles/images/products/sp.jpg'));
});

it('is idempotent and skips existing objects unless force', function () {
    Storage::disk('s3')->put('site-images/migrate-fixture-idem.svg', 'original');
    makeLocalFixture('images/migrate-fixture-idem.svg', 'newer');

    $this->artisan('media:migrate-to-s3')->assertExitCode(0);
    expect(Storage::disk('s3')->get('site-images/migrate-fixture-idem.svg'))->toBe('original');

    $this->artisan('media:migrate-to-s3 --force')->assertExitCode(0);
    expect(Storage::disk('s3')->get('site-images/migrate-fixture-idem.svg'))->toBe('newer');

    unlink(public_path('images/migrate-fixture-idem.svg'));
});

it('does nothing on dry run', function () {
    makeLocalFixture('images/migrate-fixture-dry.svg', 'svg');

    $this->artisan('media:migrate-to-s3 --dry-run')->assertExitCode(0);
    Storage::disk('s3')->assertMissing('site-images/migrate-fixture-dry.svg');

    unlink(public_path('images/migrate-fixture-dry.svg'));
});

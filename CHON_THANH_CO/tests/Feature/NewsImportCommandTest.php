<?php

use App\Models\News;
use App\Models\NewsCategory;
use Database\Seeders\NewsSeeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * @return array{0: string, 1: string} [fixture dir, temp public root]
 */
function newsImportSetup(): array
{
    $fixture = base_path('tests/Fixtures/news-import');
    $publicRoot = storage_path('framework/testing/news-import-public');

    if (is_dir($publicRoot)) {
        (new Filesystem)->deleteDirectory($publicRoot);
    }

    mkdir($publicRoot, 0755, true);

    // Storage::fake không kế thừa config url của disk — truyền thẳng url vào fake.
    Storage::fake('s3', ['url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
    config()->set('news_import.image_map', $fixture.'/image-map.json');
    config()->set('news_import.public_root', $publicRoot);
    config()->set('news_import.sources', [
        'thaichau' => [
            'label' => 'Thái Châu',
            'csv' => $fixture.'/thaichau-news.csv',
            'images' => $fixture.'/images/thaichau',
            'target' => 'thaichau',
            'web_path' => '/userfiles/images/news/thaichau',
        ],
        'bactham' => [
            'label' => 'Bách Thạch',
            'csv' => $fixture.'/bactham-news.csv',
            'images' => $fixture.'/images/bactham',
            'target' => 'bactham',
            'web_path' => '/userfiles/images/news/bactham',
        ],
    ]);

    return [$fixture, $publicRoot];
}

it('imports filtered articles, creates source categories and prefixed slugs', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    expect(News::count())->toBe(3);

    $slugs = News::pluck('slug')->all();
    expect($slugs)->toContain('thaichau-ung-dung-vai-dia-ky-thuat-duong-cao-to')
        ->toContain('bactham-ro-da-bao-ve-bo-song')
        ->toContain('bactham-tham-3d-chong-xoi-mon');

    expect(NewsCategory::pluck('slug')->all())
        ->toContain('tin-tuc-chung')
        ->toContain('ro-da')
        ->toContain('tham-3d');
});

it('inserts articles in ascending date order so id desc equals newest first', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    $dates = News::orderBy('id')->pluck('published_at')->map(fn ($d) => (string) $d)->all();

    $sorted = $dates;
    sort($sorted);

    expect($dates)->toBe($sorted)
        ->and(News::orderByDesc('id')->first()->published_at->toDateString())->toBe('2024-03-01');
});

it('sanitizes content and rewrites every image to an s3 url', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    $article = News::where('slug', 'thaichau-ung-dung-vai-dia-ky-thuat-duong-cao-to')->firstOrFail();

    expect($article->content)
        ->not->toContain('<script')
        ->not->toContain('<iframe')
        ->not->toContain('srcset')
        ->toContain('/images/news/thaichau/thaichau-101-1.png')
        ->not->toContain('https://thaichau.vn/wp-content')
        ->not->toContain('thaichau.vn//wp-content');

    expect($article->image)
        ->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/news/thaichau/thaichau-101-1.png');

    preg_match_all('/src="([^"]+)"/', $article->content, $matches);
    foreach ($matches[1] as $src) {
        expect($src)->toStartWith('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/news/');
    }
});

it('copies image files onto s3', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    Storage::disk('s3')->assertExists('images/news/thaichau/thaichau-101-1.png');
});

it('falls back to plain text when the HTML column is empty', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    $article = News::where('slug', 'bactham-ro-da-bao-ve-bo-song')->firstOrFail();

    expect($article->content)->toStartWith('Rọ đá gia cố mái taluy')
        ->and($article->content)->toContain('Nguồn: Bách Thạch');
});

it('removes images that are absent from the image map', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    $article = News::where('slug', 'bactham-tham-3d-chong-xoi-mon')->firstOrFail();

    expect($article->content)->not->toContain('<img')
        ->not->toContain('khong-co-trong-map.jpg');
});

it('appends an attribution line with the source link', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    $article = News::where('slug', 'bactham-ro-da-bao-ve-bo-song')->firstOrFail();

    expect($article->content)
        ->toContain('Nguồn: Bách Thạch')
        ->toContain('https://bactham.vn/ro-da/ro-da-bao-ve-bo-song/');
});

it('writes nothing to the database on a dry run', function () {
    newsImportSetup();

    $this->artisan('news:import --dry-run')->assertSuccessful();

    expect(News::count())->toBe(0)
        ->and(NewsCategory::count())->toBe(0);
});

it('does not copy images on a dry run', function () {
    newsImportSetup();

    $this->artisan('news:import --dry-run')->assertSuccessful();

    Storage::disk('s3')->assertMissing('images/news/thaichau/thaichau-101-1.png');
});

it('is idempotent when run twice', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();
    $firstIds = News::orderBy('slug')->pluck('id')->all();

    $this->artisan('news:import')->assertSuccessful();

    expect(News::count())->toBe(3)
        ->and(News::orderBy('slug')->pluck('id')->all())->toBe($firstIds);
});

it('soft-deletes and re-imports previously imported articles with --fresh', function () {
    newsImportSetup();

    $this->artisan('news:import')->assertSuccessful();

    News::query()->delete();
    expect(News::count())->toBe(0);

    $this->artisan('news:import --fresh')->assertSuccessful();

    expect(News::count())->toBe(3)
        ->and(News::onlyTrashed()->count())->toBe(0);
});

it('prunes the seeded placeholder articles by default', function () {
    newsImportSetup();

    $this->seed(NewsSeeder::class);

    $this->artisan('news:import')->assertSuccessful();

    expect(News::withTrashed()->where('slug', 'giai-phap-gia-co-nen-dat-yeu')->first()?->trashed())->toBeTrue();
});

it('keeps the seeded articles when --prune-seeded=0', function () {
    newsImportSetup();

    $this->seed(NewsSeeder::class);

    $this->artisan('news:import --prune-seeded=0')->assertSuccessful();

    expect(News::withTrashed()->where('slug', 'giai-phap-gia-co-nen-dat-yeu')->first()?->trashed())->toBeFalse();
});

it('skips out-of-topic articles and articles below the minimum word count', function () {
    newsImportSetup();

    $this->artisan('news:import --report='.storage_path('framework/testing/report.csv'))
        ->assertSuccessful();

    $report = array_map('str_getcsv', file(storage_path('framework/testing/report.csv'), FILE_IGNORE_NEW_LINES));
    $reasons = collect($report)->skip(1)->map(fn ($row) => $row[1].' => '.$row[4]);

    expect($reasons->contains(fn ($line) => str_starts_with($line, 'thong-bao-ngan') && str_contains($line, 'bài quá ngắn')))->toBeTrue()
        ->and($reasons->contains(fn ($line) => str_starts_with($line, 'cach-cham-soc-cay-canh') && str_contains($line, 'ngoài chủ đề')))->toBeTrue();
});

it('limits the number of imported articles with --limit', function () {
    newsImportSetup();

    $this->artisan('news:import --site=bactham --limit=1')->assertSuccessful();

    expect(News::count())->toBe(1)
        ->and(News::first()->slug)->toBe('bactham-ro-da-bao-ve-bo-song');
});

it('rejects an unknown source site', function () {
    newsImportSetup();

    $this->artisan('news:import --site=khong-ton-tai')->assertFailed();
});

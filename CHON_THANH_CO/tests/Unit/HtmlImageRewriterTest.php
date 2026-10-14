<?php

use App\Support\NewsImport\HtmlImageRewriter;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Storage::fake không kế thừa config url của disk — truyền thẳng url vào fake.
    Storage::fake('s3', ['url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

function newsImageMapFixture(): string
{
    $path = sys_get_temp_dir().'/news-image-map-'.uniqid().'.json';
    file_put_contents($path, json_encode([
        'site-a' => [
            '42' => [
                ['index' => 0, 'url' => 'https://src.example.com/pic.jpg', 'fileName' => 'pic.jpg'],
            ],
        ],
    ]));

    return $path;
}

it('copies source image to s3 and rewrites img src to public url', function () {
    $srcDir = sys_get_temp_dir().'/news-src-'.uniqid();
    mkdir($srcDir, 0755, true);
    file_put_contents($srcDir.'/pic.jpg', 'jpeg-bytes');

    config(['news_import.sources.site-a.images' => $srcDir]);

    $rewriter = new HtmlImageRewriter(newsImageMapFixture(), true, public_path());
    $result = $rewriter->rewrite(
        '<p>x</p><img src="https://src.example.com/pic.jpg" alt="a">',
        'site-a',
        '42'
    );

    Storage::disk('s3')->assertExists('images/news/site-a/site-a-42-0.jpg');
    expect($result['html'])->toContain('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/news/site-a/site-a-42-0.jpg')
        ->and($result['images'])->toHaveCount(1);

    array_map('unlink', glob($srcDir.'/*'));
    rmdir($srcDir);
});

it('removes images that are not in the map', function () {
    $rewriter = new HtmlImageRewriter(sys_get_temp_dir().'/missing-map.json', false, public_path());
    $result = $rewriter->rewrite('<img src="https://other.example.com/x.jpg">', 'site-a', '1');

    expect($result['html'])->not->toContain('other.example.com');
});

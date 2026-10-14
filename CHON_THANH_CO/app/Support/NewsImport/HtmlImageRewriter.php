<?php

namespace App\Support\NewsImport;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Thay mọi <img> trong HTML bằng bản đã copy lên S3 (key images/news/...).
 * Ảnh không có trong image-map.json sẽ bị xoá hẳn — không để lại URL ngoài.
 */
class HtmlImageRewriter
{
    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    private array $index = [];

    private bool $loaded = false;

    public function __construct(
        private readonly string $mapPath,
        private readonly bool $copyImages = true,
        private readonly string $publicRoot = '',
    ) {}

    /**
     * @return array<string, array{index: int, url: string, fileName: string}>
     */
    public function mapFor(string $site, string $postId): array
    {
        $this->load();

        $entries = $this->index[$site][(string) $postId] ?? [];
        $result = [];

        foreach ($entries as $key => $entry) {
            $result[$key] = [
                'index' => (int) $entry['index'],
                'url' => (string) $entry['url'],
                'fileName' => (string) $entry['fileName'],
            ];
        }

        return $result;
    }

    /**
     * @return array<string, string|null> web path của các ảnh trong bài (theo thứ tự index)
     */
    public function rewrite(string $html, string $site, string $postId): array
    {
        $this->load();

        $map = $this->mapFor($site, $postId);

        if (trim($html) === '') {
            return ['html' => $html, 'images' => []];
        }

        $lookup = [];

        foreach ($map as $entry) {
            $lookup[$this->normalizeUrl($entry['url'])] = $entry;
        }

        $crawler = new Crawler($html);
        $used = [];

        $crawler->filter('img')->each(function (Crawler $node) use ($lookup, $site, $postId, &$used): void {
            $dom = $node->getNode(0);

            if ($dom === null) {
                return;
            }

            $src = trim((string) ($dom->getAttribute('src') ?: $dom->getAttribute('data-src')));
            $entry = $src === '' ? null : ($lookup[$this->normalizeUrl($src)] ?? null);

            if ($entry === null) {
                $dom->parentNode?->removeChild($dom);

                return;
            }

            $target = $this->copy($site, $postId, $entry);

            if ($target === null) {
                $dom->parentNode?->removeChild($dom);

                return;
            }

            $alt = trim((string) $dom->getAttribute('alt'));
            $dom->setAttribute('src', $target);
            $dom->setAttribute('loading', 'lazy');

            if ($alt === '') {
                $dom->setAttribute('alt', '');
            }

            $used[$entry['index']] = $target;
        });

        ksort($used);

        return ['html' => $crawler->html(), 'images' => array_values($used)];
    }

    /**
     * Copy ảnh lên S3, trả về public URL hoặc null nếu thất bại.
     *
     * @param  array{index: int, url: string, fileName: string}  $entry
     */
    private function copy(string $site, string $postId, array $entry): ?string
    {
        $key = 'images/news/'.$site.'/'.$site.'-'.$postId.'-'.$entry['index'].'.'
            .(strtolower(pathinfo($entry['fileName'], PATHINFO_EXTENSION)) ?: 'jpg');

        if (! $this->copyImages) {
            return Storage::disk('s3')->url($key);
        }

        $sourceDir = $this->imagesDirFor($site);

        if ($sourceDir === null) {
            return null;
        }

        $source = $sourceDir.'/'.$entry['fileName'];

        if (! is_file($source)) {
            return null;
        }

        if (! Storage::disk('s3')->exists($key)) {
            Storage::disk('s3')->put($key, (string) file_get_contents($source));
        }

        return Storage::disk('s3')->url($key);
    }

    private function imagesDirFor(string $site): ?string
    {
        $configured = config("news_import.sources.{$site}.images");

        if (! is_string($configured) || ! is_dir($configured)) {
            return null;
        }

        return rtrim($configured, '/');
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        if (! is_file($this->mapPath)) {
            return;
        }

        $decoded = json_decode((string) file_get_contents($this->mapPath), true);

        if (! is_array($decoded)) {
            return;
        }

        foreach ($decoded as $site => $posts) {
            if (! is_array($posts)) {
                continue;
            }

            foreach ($posts as $postId => $entries) {
                if (! is_array($entries)) {
                    continue;
                }

                foreach ($entries as $entry) {
                    $url = (string) ($entry['url'] ?? '');
                    $fileName = (string) ($entry['fileName'] ?? '');

                    if ($url === '' || $fileName === '') {
                        continue;
                    }

                    $this->index[$site][(string) $postId][$this->normalizeUrl($url)] = [
                        'index' => (int) ($entry['index'] ?? 0),
                        'url' => $url,
                        'fileName' => $fileName,
                    ];
                }
            }
        }
    }

    /**
     * Nguồn hay dùng "https://thaichau.vn//wp-content/..." (2 dấu /) nên cần chuẩn hoá.
     */
    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        $url = preg_replace('#^(https?:)?//#i', '', $url) ?? $url;
        $url = preg_replace('#/+#', '/', $url) ?? $url;

        return strtolower($url);
    }
}

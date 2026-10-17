<?php

namespace App\Support\NewsImport;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Pipeline import: đọc CSV từng dòng → lọc chủ đề → sanitize + copy ảnh →
 * upsert chuyên mục và bài viết theo slug.
 */
class NewsImporter
{
    /**
     * Báo cáo các bài bị loại, tối đa giữ lại để in ra console.
     */
    private const MAX_REPORTED_SKIPS = 500;

    private ContentSanitizer $sanitizer;

    private HtmlImageRewriter $rewriter;

    private TopicFilter $filter;

    public int $created = 0;

    public int $updated = 0;

    public int $restored = 0;

    public int $skipped = 0;

    public int $rowsFailed = 0;

    /**
     * @var list<array{site: string, slug: string, title: string, category: string, reason: string}>
     */
    public array $skippedDetails = [];

    public function __construct(private readonly array $options = [])
    {
        $this->sanitizer = new ContentSanitizer;
        $this->filter = new TopicFilter;
        $this->rewriter = new HtmlImageRewriter(
            (string) config('news_import.image_map'),
            ! ($this->options['skip_images'] ?? false),
        );
    }

    /**
     * @param  callable(string, string): void|null  $log
     */
    public function run(?callable $log = null): void
    {
        $dryRun = (bool) ($this->options['dry_run'] ?? false);
        $sites = $this->sites();

        if (! $dryRun && ($this->options['fresh'] ?? false)) {
            $this->freshImported($sites);
        }

        $prepared = [];

        foreach ($sites as $site) {
            foreach ($this->readSite($site, $log) as $payload) {
                $payload['site'] = $site;
                $prepared[] = $payload;
            }
        }

        // `id desc` là thứ tự keyset của API, nên insert tăng dần ngày đăng.
        usort($prepared, static function (array $a, array $b): int {
            return [$a['published_at'], $a['site'], $a['post_id']] <=> [$b['published_at'], $b['site'], $b['post_id']];
        });

        if (! $dryRun) {
            $categoryMap = $this->syncCategories(
                Collection::make($prepared)->pluck('category_name')->filter()->unique()->values()->all()
            );

            foreach ($prepared as $payload) {
                $this->upsert($payload['site'], $payload, $categoryMap);
            }
        }

        if (! $dryRun && ($this->options['prune_seeded'] ?? true)) {
            $this->pruneSeeded();
        }
    }

    /**
     * @return list<string>
     */
    private function sites(): array
    {
        $site = (string) ($this->options['site'] ?? 'all');
        $available = array_keys((array) config('news_import.sources', []));

        if ($site === '' || $site === 'all') {
            return array_values($available);
        }

        if (! in_array($site, $available, true)) {
            throw new \RuntimeException("Nguồn không hợp lệ: {$site}. Hợp lệ: ".implode(', ', $available));
        }

        return [$site];
    }

    /**
     * @param  callable(string, string): void|null  $log
     */
    private function readSite(string $site, ?callable $log): array
    {
        $source = config("news_import.sources.{$site}");

        if (! is_array($source)) {
            return [];
        }

        if ($log !== null) {
            $log($site, 'Bắt đầu đọc '.$source['csv']);
        }

        $reader = new CsvReader;
        $prepared = [];
        $limit = (int) ($this->options['limit'] ?? 0);
        $minWords = isset($this->options['min_words']) && $this->options['min_words'] !== ''
            ? (int) $this->options['min_words']
            : (int) config('news_import.min_words');
        $config = array_merge((array) config('news_import'), ['min_words' => $minWords]);

        foreach ($reader->rows((string) $source['csv']) as $row) {
            if ($limit > 0 && count($prepared) >= $limit) {
                break;
            }

            $decision = $this->filter->decide($row, $config);

            if (! $decision['keep']) {
                $this->skipped++;
                $this->recordSkip($site, $row, $decision['reason']);

                continue;
            }

            $payload = $this->buildPayload($site, $row, (array) $source);

            if ($payload === null) {
                $this->skipped++;
                $this->recordSkip($site, $row, 'không tạo được nội dung');

                continue;
            }

            $prepared[] = $payload;
        }

        $this->rowsFailed += $reader->rowsFailed;

        if ($log !== null) {
            $log($site, sprintf(
                'Giữ %d bài, loại %d, lỗi dòng %d',
                count($prepared),
                $this->skipped,
                $reader->rowsFailed
            ));
        }

        return $prepared;
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<string, string>  $source
     * @return array<string, mixed>|null
     */
    private function buildPayload(string $site, array $row, array $source): ?array
    {
        $postId = trim((string) ($row['ID'] ?? ''));
        $title = trim(strip_tags((string) ($row['Tiêu đề'] ?? '')));
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? $title);
        $maxTitle = (int) config('news_import.max_title', 250);

        if ($title === '' || $postId === '') {
            return null;
        }

        if (mb_strlen($title) > $maxTitle) {
            $title = rtrim(mb_substr($title, 0, $maxTitle - 1)).'…';
        }

        $text = trim((string) ($row['Nội dung (text)'] ?? ''));
        $excerpt = $this->buildExcerpt((string) ($row['Mô tả ngắn'] ?? ''), $text);
        [$content, $inlineImages] = $this->buildContent($site, $postId, (string) ($row['Nội dung (HTML)'] ?? ''), $text);

        if ($content === '') {
            return null;
        }

        $content = $this->appendAttribution($content, $site, $source, (string) ($row['Đường dẫn'] ?? ''));

        return [
            'post_id' => $postId,
            'slug' => trim((string) ($row['Slug'] ?? '')),
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'image' => $inlineImages[0] ?? (string) config('news_import.fallback_images.0', '/images/products/industrial-1.jpg'),
            'published_at' => $this->parseDate((string) ($row['Ngày đăng'] ?? '')),
            'category_name' => $this->primaryCategory((string) ($row['Chuyên mục'] ?? '')),
        ];
    }

    /**
     * @return array{0: string, 1: list<string>} [content, danh sách web path ảnh inline]
     */
    private function buildContent(string $site, string $postId, string $html, string $text): array
    {
        $html = trim($html);

        if ($html !== '') {
            $rewritten = $this->rewriter->rewrite($html, $site, $postId);
            $clean = $this->sanitizer->sanitize($rewritten['html']);

            if ($clean !== '' && strip_tags($clean) !== '') {
                // Ảnh đã bị rewriter xoá (không có trong image-map) không được đưa vào content.
                $clean = preg_replace('#<img[^>]+src="(?![^"]*/images/news/)[^"]*"[^>]*>\s*#i', '', $clean) ?? $clean;
                $images = $this->extractInlineImages($clean);

                if ($images !== []) {
                    return [$clean, $images];
                }

                if (trim(strip_tags($clean)) !== '') {
                    return [$clean, $rewritten['images']];
                }
            }
        }

        return [trim(preg_replace('/\n{3,}/', "\n\n", $text) ?? $text), []];
    }

    /**
     * Trả về danh sách web path ảnh inline đã xử lý, theo thứ tự xuất hiện.
     *
     * @return list<string>
     */
    private function extractInlineImages(string $content): array
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/i', $content, $matches);

        return array_values(array_unique(array_filter($matches[1] ?? [])));
    }

    private function buildExcerpt(string $description, string $text): string
    {
        $description = trim(preg_replace('/\s+/u', ' ', strip_tags($description)) ?? '');
        $maxExcerpt = (int) config('news_import.max_excerpt', 500);

        $needsFallback = $description === ''
            || preg_match('/(\[\.{3}\]|\[\x{2026}\]|\.{3}|\x{2026})$/u', $description) === 1;

        if ($needsFallback) {
            $sentences = preg_split('/(?<=[.!?])\s+/u', trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '')) ?: [];
            $description = trim(implode(' ', array_slice($sentences, 0, 3)));
        }

        if ($description === '') {
            $description = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');
        }

        if (mb_strlen($description) > $maxExcerpt) {
            $description = rtrim(mb_substr($description, 0, $maxExcerpt - 1)).'…';
        }

        return $description;
    }

    private function appendAttribution(string $content, string $site, array $source, string $url): string
    {
        if (! config('news_import.attribution.enabled', true)) {
            return $content;
        }

        $url = trim($url);
        $label = (string) ($source['label'] ?? $site);
        $host = $url !== '' ? (string) (parse_url($url, PHP_URL_HOST) ?: $site) : $site;

        $line = strtr((string) config('news_import.attribution.template'), [
            '{label}' => $label,
            '{url}' => $url,
            '{host}' => $host,
        ]);

        return rtrim($content)."\n".$line;
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function primaryCategory(string $raw): ?string
    {
        $parts = preg_split('/\s*\|\s*/', trim($raw)) ?: [];
        $name = trim((string) ($parts[0] ?? ''));

        if ($name === '' || strtolower($name) === 'unccategorized') {
            return null;
        }

        return Str::of($name)->replaceMatches('/@vi$/i', '')->trim()->value() ?: null;
    }

    /**
     * Tạo/cập nhật chuyên mục theo tên nguồn, giữ nguyên 4 chuyên mục seed.
     *
     * @param  list<string>  $names
     * @return array<string, int> tên chuyên mục => id
     */
    private function syncCategories(array $names): array
    {
        $map = [];

        if ($names === []) {
            return $map;
        }

        $maxOrder = (int) NewsCategory::query()->max('sort_order');
        $order = max($maxOrder, 0);

        foreach ($names as $name) {
            $slug = $this->uniqueCategorySlug($name);

            $category = NewsCategory::withTrashed()->where('slug', $slug)->first();

            if ($category) {
                if ($category->trashed()) {
                    $category->restore();
                }

                $category->update(['name' => $name, 'is_active' => true]);
            } else {
                $order++;
                $category = NewsCategory::create([
                    'slug' => $slug,
                    'name' => $name,
                    'sort_order' => $order,
                    'is_active' => true,
                ]);
            }

            $map[$name] = $category->id;
        }

        return $map;
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name, '-', 'vi');

        if ($base === '') {
            $base = 'chuyen-muc';
        }

        $base = Str::limit($base, 140, '');
        $slug = $base;
        $suffix = 2;

        while (NewsCategory::withTrashed()->where('slug', $slug)->where('name', '!=', $name)->exists()) {
            $slug = $base.'-'.$suffix++;

            if ($suffix > 50) {
                break;
            }
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, int>  $categoryMap
     */
    private function upsert(string $site, array $payload, array $categoryMap): void
    {
        $slug = $this->uniqueNewsSlug($site, $payload);
        $attributes = [
            'news_category_id' => $categoryMap[$payload['category_name']] ?? null,
            'title' => $payload['title'],
            'excerpt' => $payload['excerpt'],
            'content' => $payload['content'],
            'image' => $payload['image'],
            'published_at' => $payload['published_at'],
            'is_active' => true,
        ];

        $existing = News::withTrashed()->where('slug', $slug)->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $this->restored++;
            } else {
                $this->updated++;
            }

            $existing->update($attributes);

            return;
        }

        News::create(array_merge(['slug' => $slug], $attributes));
        $this->created++;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function uniqueNewsSlug(string $site, array $payload): string
    {
        $max = (int) config('news_import.max_slug', 120);
        $base = $site.'-'.(string) ($payload['slug'] ?? '');

        // Slug quá dài luôn gắn postId để ổn định và không đụng nhau giữa các lần chạy.
        if (mb_strlen($base) > $max) {
            return Str::limit($base, $max - strlen((string) $payload['post_id']) - 1, '').'-'.$payload['post_id'];
        }

        return $base;
    }

    /**
     * @param  list<string>  $sites
     */
    private function freshImported(array $sites): void
    {
        foreach ($sites as $site) {
            News::withTrashed()
                ->where('slug', 'like', $site.'-%')
                ->get()
                ->each(static fn (News $news) => $news->delete());

            NewsCategory::withTrashed()
                ->whereNotIn('slug', (array) config('news_import.keep_category_slugs', []))
                ->get()
                ->each(static fn (NewsCategory $category) => $category->delete());
        }
    }

    private function pruneSeeded(): void
    {
        News::withTrashed()
            ->whereIn('slug', (array) config('news_import.prune_slugs', []))
            ->get()
            ->each(static fn (News $news) => $news->delete());
    }

    /**
     * @param  array<string, string>  $row
     */
    private function recordSkip(string $site, array $row, string $reason): void
    {
        if (count($this->skippedDetails) >= self::MAX_REPORTED_SKIPS) {
            return;
        }

        $this->skippedDetails[] = [
            'site' => $site,
            'slug' => (string) ($row['Slug'] ?? ''),
            'title' => trim((string) ($row['Tiêu đề'] ?? '')),
            'category' => (string) ($row['Chuyên mục'] ?? ''),
            'reason' => $reason,
        ];
    }
}

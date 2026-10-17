<?php

namespace App\Console\Commands;

use App\Support\MediaUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MediaMigrateToS3 extends Command
{
    protected $signature = 'media:migrate-to-s3 {--dry-run} {--force}';

    protected $description = 'Copy toàn bộ media local lên Supabase S3 và rewrite DB paths thành S3 keys';

    /** @var array<string, string> local root (relative to base_path) => S3 prefix ('' = root của bucket) */
    private const ROOT_MAP = [
        'public/userfiles/images' => 'images',
        'public/userfiles/files' => 'files',
        'public/images' => 'site-images',
        'public/documents' => 'documents',
        'storage/app/public' => '',
    ];

    private const PATH_REPLACEMENTS = [
        '/userfiles/images/' => 'images/',
        '/userfiles/files/' => 'files/',
        '/userfiles/' => '',
        '/images/' => 'site-images/',
        '/documents/' => 'documents/',
        '/storage/' => '',
    ];

    private const COLUMN_MEDIA = [
        'products' => ['image'],
        'product_images' => ['image'],
        'projects' => ['hero_image', 'desc_image'],
        'project_images' => ['image'],
        'certificates' => ['image', 'file'],
        'news' => ['image'],
        'project_materials' => ['image'],
        'banners' => ['image'],
        'partners' => ['image'],
        'product_documents' => ['file_url'],
    ];

    private int $uploaded = 0;

    private int $skipped = 0;

    private int $failed = 0;

    private int $rowsUpdated = 0;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (config('filesystems.disks.s3.url') === null) {
            $this->error('AWS_URL chưa được set — Storage::url() sẽ sai. Thêm AWS_URL vào .env rồi chạy lại.');

            return self::FAILURE;
        }

        $this->uploadLocalFiles($dryRun, $force);
        $this->rewriteDatabase($dryRun);

        $this->info("Upload: {$this->uploaded} mới, {$this->skipped} bỏ qua, {$this->failed} lỗi. DB: {$this->rowsUpdated} rows updated.");

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function uploadLocalFiles(bool $dryRun, bool $force): void
    {
        $disk = Storage::disk('s3');

        foreach (self::ROOT_MAP as $localRoot => $prefix) {
            $absolute = base_path($localRoot);

            if (! is_dir($absolute)) {
                $this->warn("Bỏ qua (không tồn tại): {$localRoot}");

                continue;
            }

            foreach (File::allFiles($absolute) as $file) {
                $relative = str_replace('\\', '/', $file->getPathname());
                $relative = substr($relative, strlen(str_replace('\\', '/', $absolute)) + 1);
                $key = ($prefix !== '' ? $prefix.'/' : '').$relative;

                if ($disk->exists($key) && ! $force) {
                    $this->skipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] {$file->getPathname()} -> s3://{$key}");
                    $this->uploaded++;

                    continue;
                }

                try {
                    $disk->put($key, file_get_contents($file->getPathname()));
                    $this->uploaded++;
                } catch (\Throwable $e) {
                    $this->failed++;
                    $this->error("Lỗi upload {$key}: {$e->getMessage()}");
                }
            }
        }
    }

    private function rewriteDatabase(bool $dryRun): void
    {
        foreach (self::COLUMN_MEDIA as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $this->warn("Bỏ qua bảng không tồn tại: {$table}");

                continue;
            }

            foreach (DB::table($table)->select('id', ...$columns)->orderBy('id')->cursor() as $row) {
                $updates = [];

                foreach ($columns as $column) {
                    $new = $this->toKey($row->{$column});

                    if ($new !== null && $new !== $row->{$column}) {
                        $updates[$column] = $new;
                    }
                }

                if ($updates !== []) {
                    $this->rowsUpdated++;

                    if (! $dryRun) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            }
        }

        // Settings (key/value)
        if (Schema::hasTable('settings')) {
            foreach (['company.capability_file', 'company.inspection_file'] as $key) {
                $row = DB::table('settings')->where('key', $key)->first();

                if ($row === null) {
                    continue;
                }

                $new = $this->toKey($row->value);

                if ($new !== null && $new !== $row->value) {
                    $this->rowsUpdated++;

                    if (! $dryRun) {
                        DB::table('settings')->where('key', $key)->update(['value' => $new]);
                    }
                }
            }
        }

        // HTML content trong news_translations: rewrite /userfiles/... thành public URL đầy đủ
        if (Schema::hasTable('news_translations') && Schema::hasColumn('news_translations', 'content')) {
            foreach (DB::table('news_translations')->select('id', 'content')->whereNotNull('content')->cursor() as $row) {
                $new = preg_replace_callback(
                    '#/(?:userfiles|storage)/[^\s"\'<>)]+#',
                    fn ($m) => MediaUrl::url($this->toKey($m[0])) ?? $m[0],
                    $row->content
                );

                if ($new !== null && $new !== $row->content) {
                    $this->rowsUpdated++;

                    if (! $dryRun) {
                        DB::table('news_translations')->where('id', $row->id)->update(['content' => $new]);
                    }
                }
            }
        }
    }

    /** Path local cũ -> S3 key; NULL nếu không phải path local. */
    private function toKey(?string $value): ?string
    {
        if ($value === null || $value === '' || str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return null;
        }

        foreach (self::PATH_REPLACEMENTS as $from => $to) {
            if (str_starts_with($value, $from)) {
                return ltrim($to.substr($value, strlen($from)), '/');
            }
        }

        return null;
    }
}

<?php

namespace App\Console\Commands;

use App\Support\NewsImport\NewsImporter;
use Illuminate\Console\Command;

class ImportNewsCommand extends Command
{
    protected $signature = 'news:import
        {--site=all : all|thaichau|bactham}
        {--dry-run : chỉ in báo cáo, không ghi DB, không copy ảnh}
        {--fresh : soft-delete bài/chuyên mục đã import trước rồi import lại}
        {--limit=0 : giới hạn số bài giữ lại (0 = không giới hạn)}
        {--min-words= : ghi đè news_import.min_words}
        {--skip-images : không copy ảnh, chỉ ghi nội dung}
        {--prune-seeded=1 : soft-delete 6 bài seed giả (1=bật, 0=tắt)}
        {--report= : ghi báo cáo ra file CSV (mặc định storage/app/news-import-report-{timestamp}.csv)}';

    protected $description = 'Import tin tức thật từ CSV đã cào sẵn (thaichau.vn, bactham.vn)';

    public function handle(): int
    {
        $site = (string) $this->option('site');

        if (! config('news_import.sources')) {
            $this->components->error('Thiếu cấu hình config/news_import.php');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $fresh = (bool) $this->option('fresh');
        $skipImages = (bool) $this->option('skip-images') || $dryRun;

        if (! is_dir((string) config('news_import.image_map')) && ! is_file((string) config('news_import.image_map'))) {
            $this->components->error('Thiếu file image-map.json — không map được ảnh.');

            return self::FAILURE;
        }

        $importer = new NewsImporter([
            'site' => $site,
            'dry_run' => $dryRun,
            'fresh' => $fresh,
            'limit' => (int) $this->option('limit'),
            'min_words' => $this->option('min-words'),
            'skip_images' => $skipImages,
            'prune_seeded' => $this->option('prune-seeded') !== '0',
        ]);

        $this->components->info($dryRun
            ? 'Chế độ dry-run: không ghi DB, không copy ảnh.'
            : 'Bắt đầu import tin tức.');

        try {
            $importer->run(function (string $source, string $message): void {
                $this->line("  <fg=gray>[{$source}]</> {$message}");
            });
        } catch (\RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=gray>Tạo mới</>', (string) $importer->created);
        $this->components->twoColumnDetail('<fg=gray>Cập nhật</>', (string) $importer->updated);
        $this->components->twoColumnDetail('<fg=gray>Khôi phục (đã xoá mềm)</>', (string) $importer->restored);
        $this->components->twoColumnDetail('<fg=gray>Bị loại</>', (string) $importer->skipped);
        $this->components->twoColumnDetail('<fg=gray>Dòng CSV lỗi</>', (string) $importer->rowsFailed);

        $this->reportSkipped($importer, $dryRun);

        return self::SUCCESS;
    }

    private function reportSkipped(NewsImporter $importer, bool $dryRun): void
    {
        if ($importer->skippedDetails === []) {
            return;
        }

        $this->newLine();
        $this->components->info('Các bài bị loại:');

        $rows = $importer->skippedDetails;

        foreach (array_slice($rows, 0, 15) as $row) {
            $this->line(sprintf(
                '  <fg=yellow>%s</> — %s <fg=gray>[%s]</>',
                $row['title'] !== '' ? $this->truncate($row['title'], 60) : $row['slug'],
                $row['reason'],
                $row['site']
            ));
        }

        if (count($rows) > 15) {
            $this->line(sprintf('  <fg=gray>... và %d bài khác, xem file báo cáo.</>', count($rows) - 15));
        }

        if ($dryRun) {
            return;
        }

        $path = (string) $this->option('report');
        $path = $path !== '' ? $path : storage_path('app/news-import-report-'.date('Ymd-His').'.csv');

        $handle = fopen($path, 'w');

        if ($handle === false) {
            $this->components->error("Không ghi được file báo cáo: {$path}");

            return;
        }

        fputcsv($handle, ['site', 'slug', 'title', 'category', 'reason']);

        foreach ($rows as $row) {
            fputcsv($handle, [$row['site'], $row['slug'], $row['title'], $row['category'], $row['reason']]);
        }

        fclose($handle);

        $this->components->info("Báo cáo đầy đủ: {$path}");
    }

    private function truncate(string $value, int $limit): string
    {
        return mb_strlen($value) <= $limit ? $value : mb_substr($value, 0, $limit - 1).'…';
    }
}

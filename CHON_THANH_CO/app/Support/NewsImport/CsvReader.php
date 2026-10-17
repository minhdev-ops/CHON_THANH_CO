<?php

namespace App\Support\NewsImport;

use Generator;

/**
 * Đọc CSV kiểu "bài nhiều dòng" (nội dung HTML chứa newline trong ô quoted)
 * bằng fgetcsv để không bao giờ nạp cả file vào RAM.
 */
class CsvReader
{
    public int $rowsRead = 0;

    public int $rowsFailed = 0;

    /**
     * @return Generator<int, array<string, string>>
     */
    public function rows(string $path): Generator
    {
        if (! is_file($path)) {
            throw new \RuntimeException("Không tìm thấy file CSV: {$path}");
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Không mở được file CSV: {$path}");
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false || $header === [null]) {
                throw new \RuntimeException("File CSV rỗng: {$path}");
            }

            $header = array_map(
                static fn ($column) => self::clean((string) $column),
                $header
            );
            $count = count($header);

            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || $row === []) {
                    continue;
                }

                if (count($row) !== $count) {
                    $this->rowsFailed++;

                    continue;
                }

                $values = array_map(static fn ($value) => self::clean((string) $value), $row);
                $this->rowsRead++;

                yield array_combine($header, $values);
            }
        } finally {
            fclose($handle);
        }
    }

    private static function clean(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
        }

        return $value;
    }
}

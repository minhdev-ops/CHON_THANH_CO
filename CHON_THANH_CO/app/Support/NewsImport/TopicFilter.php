<?php

namespace App\Support\NewsImport;

use Illuminate\Support\Str;

/**
 * Quyết định giữ / loại một bài dựa trên chuyên mục nguồn, từ khoá và độ dài.
 */
class TopicFilter
{
    /**
     * @param  array<string, string>  $row
     * @param  array<string, mixed>  $config
     * @return array{keep: bool, reason: string}
     */
    public function decide(array $row, array $config): array
    {
        $title = trim($row['Tiêu đề'] ?? '');
        $text = trim($row['Nội dung (text)'] ?? '');
        $html = trim($row['Nội dung (HTML)'] ?? '');

        if ($title === '') {
            return ['keep' => false, 'reason' => 'thiếu tiêu đề'];
        }

        if ($text === '' && $html === '') {
            return ['keep' => false, 'reason' => 'thiếu nội dung'];
        }

        $minWords = (int) ($config['min_words'] ?? 0);

        if ($minWords > 0) {
            $declared = (int) ($row['Số từ'] ?? 0);

            if ($declared > 0 && $declared < $minWords) {
                return ['keep' => false, 'reason' => "bài quá ngắn ({$declared} từ < {$minWords})"];
            }

            if ($declared === 0 && $this->countWords($text) < $minWords) {
                return ['keep' => false, 'reason' => "bài quá ngắn (< {$minWords} từ)"];
            }
        }

        if ($this->matchesCategory($row['Chuyên mục'] ?? '', $config)) {
            return ['keep' => true, 'reason' => 'khớp chuyên mục nguồn'];
        }

        $haystack = self::normalize($title.' '.$row['Mô tả ngắn'].' '.$text);

        foreach ((array) ($config['keep_keywords'] ?? []) as $keyword) {
            $keyword = self::normalize((string) $keyword);

            if ($keyword !== '' && str_contains($haystack, $keyword)) {
                return ['keep' => true, 'reason' => "khớp từ khoá: {$keyword}"];
            }
        }

        return ['keep' => false, 'reason' => 'ngoài chủ đề vật liệu địa kỹ thuật'];
    }

    /**
     * Chuyên mục nguồn có thể là danh sách phân tách bằng " | ".
     */
    private function matchesCategory(string $raw, array $config): bool
    {
        $allowed = array_merge(
            (array) ($config['allowed_source_categories'] ?? []),
            (array) ($config['always_keep_categories'] ?? []),
        );

        if ($allowed === []) {
            return false;
        }

        $allowedNormalized = array_map(self::normalize(...), array_map(strval(...), $allowed));
        $parts = preg_split('/\s*\|\s*/', $raw) ?: [];

        foreach ($parts as $part) {
            if (in_array(self::normalize($part), $allowedNormalized, true)) {
                return true;
            }
        }

        return false;
    }

    private function countWords(string $text): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags($text))) ?: [];

        return count(array_filter($words, static fn ($word) => trim($word) !== ''));
    }

    /**
     * Bỏ dấu, hạ chữ thường và bỏ dấu câu để so khớp ổn định.
     */
    public static function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = Str::ascii($value);
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }
}

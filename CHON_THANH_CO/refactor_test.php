<?php

$dir = '/home/wanmin/1.Work/03.Project/2026/CHON_THANH_CO/CHON_THANH_CO/resources/views/admin';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $files[] = $file->getPathname();
    }
}

echo "Found " . count($files) . " blade files.\n";

foreach ($files as $filePath) {
    $content = file_get_contents($filePath);
    $original = $content;

    // 1. Regex replacements for translation calls:
    // Pattern 1: ->translation('vi')->... or ->translation('vi')?->...
    // Pattern 2: ->translation('en')->... or ->translation('en')?->...
    // Pattern 3: ?->translation('vi')->... or ?->translation('vi')?->...
    // Pattern 4: ?->translation('en')->... or ?->translation('en')?->...
    
    $patterns = [
        '/->translation\(\'(?:vi|en)\'\)\?->([a-zA-Z0-9_]+)/',
        '/\?->translation\(\'(?:vi|en)\'\)\?->([a-zA-Z0-9_]+)/',
        '/->translation\(\'(?:vi|en)\'\)->([a-zA-Z0-9_]+)/',
        '/\?->translation\(\'(?:vi|en)\'\)->([a-zA-Z0-9_]+)/',
    ];

    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '->$1', $content);
        $content = preg_replace(str_replace('->', '?->', $pattern), '?->$1', $content); // just in case
    }
    
    // Also handle specific occurrences like $s->translation('vi')?->label
    $content = preg_replace('/\$([a-zA-Z0-9_]+)\?->translation\(\'(?:vi|en)\'\)\?->([a-zA-Z0-9_]+)/', '$\1?->\2', $content);
    $content = preg_replace('/\$([a-zA-Z0-9_]+)->translation\(\'(?:vi|en)\'\)\?->([a-zA-Z0-9_]+)/', '$\1?->\2', $content);

    // 2. Handle translation-section blocks in form views.
    // Let's check if the file contains <x-admin.partials.translation-section
    if (str_contains($content, '<x-admin.partials.translation-section')) {
        // We can extract fields or replace the language toggle + translation sections with clean inputs.
        // Let's inspect which file this is and transform accordingly.
        $relativePath = str_replace($dir . '/', '', $filePath);
        echo "Refactoring translation-section in: $relativePath\n";
    }

    if ($content !== $original) {
        file_put_contents($filePath, $content);
        echo "Updated: " . basename($filePath) . "\n";
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /**
     * Chuyển S3 key tương đối thành public URL; input đã là URL thì giữ nguyên.
     */
    public static function url(?string $key): ?string
    {
        if ($key === null || trim($key) === '') {
            return null;
        }

        $key = trim($key);

        if (str_starts_with($key, 'http://') || str_starts_with($key, 'https://')) {
            return $key;
        }

        return Storage::disk('s3')->url(ltrim($key, '/'));
    }
}

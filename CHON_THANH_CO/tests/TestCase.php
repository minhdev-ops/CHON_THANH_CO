<?php

namespace Tests;

use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tạo ứng dụng cho test.
     *
     * Khi chạy qua `php artisan test`, app đã được boot lần đầu từ `.env`
     * (APP_ENV=local) nên các env vars trong phpunit.xml không được Laravel
     * áp dụng (Laravel đọc $_ENV/$_SERVER ưu tiên hơn getenv). Hậu quả: test
     * chạy trên MySQL thật và RefreshDatabase có thể xoá dữ liệu thật.
     *
     * → Force toàn bộ env vars test TRƯỚC khi boot app.
     */
    public function createApplication(): Application
    {
        $this->forceTestingEnv();

        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function forceTestingEnv(): void
    {
        // Test đầu tiên trong mỗi tiến trình được boot TRƯỚC khi Laravel nạp .env,
        // nên env('DB_TEST_PASSWORD') trả '' → DB_PASSWORD bị ép rỗng → MySQL từ chối
        // kết nối ("using password: NO"). Nạp .env thủ công trước khi đọc.
        if (! getenv('DB_TEST_PASSWORD') && file_exists(dirname(__DIR__).'/.env')) {
            Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
        }

        $vars = [
            'APP_ENV' => 'testing',
            'APP_MAINTENANCE_DRIVER' => 'file',
            'BCRYPT_ROUNDS' => '4',
            'BROADCAST_CONNECTION' => 'null',
            'CACHE_STORE' => 'array',
            // Dùng database test riêng (MySQL) — KHÔNG bao giờ chạm DB thật.
            // (pdo_sqlite không có sẵn trong môi trường này nên không dùng sqlite :memory:)
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => env('DB_HOST', '127.0.0.1'),
            'DB_PORT' => env('DB_PORT', '3306'),
            'DB_DATABASE' => env('DB_TEST_DATABASE', 'chon_thanh_co_test'),
            'DB_USERNAME' => env('DB_TEST_USERNAME', env('DB_USERNAME', 'root')),
            'DB_PASSWORD' => env('DB_TEST_PASSWORD', env('DB_PASSWORD', '')),
            'DB_URL' => '',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'PULSE_ENABLED' => 'false',
            'TELESCOPE_ENABLED' => 'false',
        ];

        foreach ($vars as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }
    }
}

<?php

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kiểm thử đồng thời (concurrency) ở tầng database.
 *
 * Dùng pcntl_fork để tạo nhiều tiến trình thật cùng lúc ghi dữ liệu vào
 * contact_messages. KHÔNG dùng RefreshDatabase ở đây vì transaction của
 * tiến trình cha không chia sẻ được với tiến trình con — ta tự quản lý
 * migrate/truncate để tránh xung đột với các suite khác.
 */

beforeEach(function () {
    if (! Schema::hasTable('contact_messages')) {
        Artisan::call('migrate');
    }
    DB::table('contact_messages')->truncate();
});

afterEach(function () {
    DB::table('contact_messages')->truncate();
});

it('inserts many rows from concurrent processes without losing data', function () {
    $children = 8;   // số tiến trình đồng thời
    $perChild = 25;  // số dòng mỗi tiến trình ghi

    $db = [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_TEST_DATABASE', 'chon_thanh_co_test'),
        'user' => env('DB_TEST_USERNAME', env('DB_USERNAME', 'root')),
        'pass' => env('DB_TEST_PASSWORD', env('DB_PASSWORD', '')),
    ];

    $pids = [];
    for ($i = 0; $i < $children; $i++) {
        $pid = pcntl_fork();
        expect($pid)->not->toBe(-1, 'pcntl_fork thất bại');

        if ($pid === 0) {
            // ── Tiến trình con ──
            // Mở kết nối PDO riêng (tránh dùng chung socket của tiến trình cha).
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $stmt = $pdo->prepare(
                'insert into contact_messages
                    (name, phone, email, message, status, created_at, updated_at)
                 values (?, ?, ?, ?, ?, now(), now())'
            );

            for ($j = 0; $j < $perChild; $j++) {
                $stmt->execute([
                    "Người gửi {$i}-{$j}",
                    '0912345678',
                    "user{$i}_{$j}@example.com",
                    'Nội dung tư vấn đồng thời từ tiến trình '.$i,
                    'new',
                ]);
            }
            exit(0);
        }

        $pids[] = $pid;
    }

    // ── Tiến trình cha: chờ tất cả con hoàn tất ──
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
        expect(pcntl_wexitstatus($status))->toBe(0, "tiến trình $pid thoát lỗi");
    }

    // Tổng số dòng phải khớp chính xác: không mất, không trùng.
    expect(ContactMessage::count())->toBe($children * $perChild);
});

it('produces unique rows when many processes insert at the same time', function () {
    $children = 5;
    $perChild = 10;

    $db = [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_TEST_DATABASE', 'chon_thanh_co_test'),
        'user' => env('DB_TEST_USERNAME', env('DB_USERNAME', 'root')),
        'pass' => env('DB_TEST_PASSWORD', env('DB_PASSWORD', '')),
    ];

    $pids = [];
    for ($i = 0; $i < $children; $i++) {
        $pid = pcntl_fork();
        if ($pid === 0) {
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->prepare(
                'insert into contact_messages
                    (name, phone, email, message, status, created_at, updated_at)
                 values (?, ?, ?, ?, ?, now(), now())'
            );
            for ($j = 0; $j < $perChild; $j++) {
                $stmt->execute([
                    "Người gửi {$i}-{$j}",
                    '0912345678',
                    "unique{$i}_{$j}@example.com",
                    'Nội dung tư vấn',
                    'new',
                ]);
            }
            exit(0);
        }
        $pids[] = $pid;
    }

    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }

    $emails = ContactMessage::pluck('email')->all();
    expect($emails)->toHaveCount($children * $perChild)
        ->and(count(array_unique($emails)))->toBe($children * $perChild); // không trùng email
});
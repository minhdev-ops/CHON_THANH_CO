<?php

use App\Models\AuditLog;
use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('adds a new setting key via the form', function () {
    $this->seed(SettingSeeder::class);

    $this->put('/admin/settings', [
        'settings' => ['company.phone' => '0901.111.222'],
        'new_group' => 'social',
        'new_key' => 'tiktok',
        'new_value' => 'https://tiktok.com/chonthanh',
    ])->assertRedirect('/admin/settings');

    expect(Setting::where('key', 'social.tiktok')->exists())->toBeTrue();
    expect(Setting::where('key', 'social.tiktok')->first()->value)->toBe('https://tiktok.com/chonthanh');
    expect(Setting::where('key', 'social.tiktok')->first()->group)->toBe('social');
});

it('ignores invalid new group when adding a key', function () {
    $this->seed(SettingSeeder::class);

    $this->put('/admin/settings', [
        'settings' => ['company.phone' => '0901.111.222'],
        'new_group' => 'hacker',
        'new_key' => 'evil',
        'new_value' => 'x',
    ])->assertRedirect('/admin/settings');

    expect(Setting::where('key', 'hacker.evil')->exists())->toBeFalse();
});

it('does not overwrite an existing key via add-new form', function () {
    $this->seed(SettingSeeder::class);

    $this->put('/admin/settings', [
        'settings' => ['company.phone' => '0901.111.222'],
        'new_group' => 'company',
        'new_key' => 'phone',
        'new_value' => 'OVERWRITE',
    ])->assertRedirect('/admin/settings');

    expect(Setting::where('key', 'company.phone')->first()->value)->toBe('0901.111.222');
});

it('filters audit logs by action and model type', function () {
    AuditLog::create([
        'actor' => 'admin',
        'action' => 'created',
        'model_type' => 'App\\Models\\Category',
        'model_id' => 1,
        'changes' => ['attributes' => ['slug' => 'x']],
    ]);
    AuditLog::create([
        'actor' => 'admin',
        'action' => 'deleted',
        'model_type' => 'App\\Models\\Product',
        'model_id' => 2,
        'changes' => ['attributes' => ['code' => 'y']],
    ]);

    $this->get('/admin/audit-logs?action=created')
        ->assertStatus(200)
        ->assertSee('Tạo mới');

    $this->get('/admin/audit-logs?model_type=App%5CModels%5CCategory')
        ->assertStatus(200);

    $this->get('/admin/audit-logs?action=created&model_type=App%5CModels%5CCategory&actor=admin')
        ->assertStatus(200);
});

it('renders the file manager index page', function () {
    $this->get('/admin/files')
        ->assertStatus(200)
        ->assertSee('Quản lý file');
});

it('renders the file picker page', function () {
    $this->get('/admin/files/picker?input=image&type=Images')
        ->assertStatus(200);
});

it('renders the dashboard with content stats', function () {
    $this->seed(SettingSeeder::class);

    $this->get('/admin')
        ->assertStatus(200)
        ->assertSee('Sản phẩm')
        ->assertSee('Cấu hình');
});

it('shows audit log model labels on the page', function () {
    AuditLog::create([
        'actor' => 'admin',
        'action' => 'created',
        'model_type' => 'App\Models\AboutTimeline',
        'model_id' => 1,
        'changes' => ['attributes' => ['sort_order' => 1]],
    ]);

    $this->get('/admin/audit-logs')
        ->assertStatus(200)
        ->assertSee('Mốc lịch sử');
});

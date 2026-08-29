<?php

use App\Mail\ContactReply;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('shows the settings form', function () {
    $this->seed(SettingSeeder::class);

    $this->get('/admin/settings')
        ->assertStatus(200)
        ->assertSee('Thông tin công ty')
        ->assertSee('company.name_vi');
});

it('updates a setting value', function () {
    $this->seed(SettingSeeder::class);
    $setting = Setting::where('key', 'company.phone')->first();

    $this->put('/admin/settings', [
        'settings' => [
            $setting->key => '0901.111.222',
        ],
    ])->assertRedirect('/admin/settings');

    expect($setting->fresh()->value)->toBe('0901.111.222');
});

it('rejects invalid JSON for the factories setting', function () {
    $this->seed(SettingSeeder::class);
    $setting = Setting::where('key', 'company.factories')->first();

    $this->put('/admin/settings', [
        'settings' => [
            $setting->key => '{invalid-json',
        ],
    ])->assertSessionHasErrors("settings.{$setting->key}");
});

it('accepts valid JSON for the factories setting', function () {
    $this->seed(SettingSeeder::class);
    $setting = Setting::where('key', 'company.factories')->first();

    $this->put('/admin/settings', [
        'settings' => [
            $setting->key => json_encode([['name' => 'Nhà máy CN1', 'location' => 'Hóc Môn', 'product' => 'Rọ đá']], JSON_UNESCAPED_UNICODE),
        ],
    ])->assertRedirect('/admin/settings');

    expect(json_decode($setting->fresh()->value, true))->toBeArray();
});

it('lists contact messages', function () {
    ContactMessage::create([
        'name' => 'Nguyễn Văn A',
        'phone' => '0912345678',
        'email' => 'a@example.com',
        'message' => 'Xin báo giá',
        'status' => 'new',
    ]);

    $this->get('/admin/contacts')->assertStatus(200)->assertSee('Nguyễn Văn A');
});

it('shows a contact message detail', function () {
    $contact = ContactMessage::create([
        'name' => 'Nguyễn Văn B',
        'phone' => '0912345679',
        'email' => 'b@example.com',
        'message' => 'Nội dung liên hệ',
        'status' => 'new',
    ]);

    $this->get("/admin/contacts/{$contact->id}")->assertStatus(200)->assertSee('Nội dung liên hệ');
});

it('marks a contact as read', function () {
    $contact = ContactMessage::create([
        'name' => 'Nguyễn Văn C',
        'phone' => '0912345670',
        'email' => 'c@example.com',
        'message' => 'Tin nhắn',
        'status' => 'new',
    ]);

    $this->post("/admin/contacts/{$contact->id}/read")->assertRedirect();

    expect($contact->fresh()->status)->toBe('replied');
    expect($contact->fresh()->handled_at)->not->toBeNull();
});

it('replies to a contact by email', function () {
    Mail::fake();

    $contact = ContactMessage::create([
        'name' => 'Nguyễn Văn D',
        'phone' => '0912345671',
        'email' => 'd@example.com',
        'message' => 'Cần tư vấn',
        'status' => 'new',
    ]);

    $this->post("/admin/contacts/{$contact->id}/reply", [
        'reply' => 'Cảm ơn bạn đã liên hệ với CHON THANH CO. Chúng tôi sẽ sớm phản hồi.',
    ])->assertRedirect();

    Mail::assertSent(ContactReply::class);
    expect($contact->fresh()->status)->toBe('replied');
});

it('deletes a contact message', function () {
    $contact = ContactMessage::create([
        'name' => 'Nguyễn Văn E',
        'phone' => '0912345672',
        'email' => 'e@example.com',
        'message' => 'Tin nhắn cần xóa',
        'status' => 'new',
    ]);

    $this->delete("/admin/contacts/{$contact->id}")->assertRedirect('/admin/contacts');

    expect(ContactMessage::find($contact->id))->toBeNull();
});

it('shows the audit logs page', function () {
    $this->get('/admin/audit-logs')->assertStatus(200);
});

it('records an audit log when creating a category', function () {
    $this->post('/admin/categories', [
        'sort_order' => 1,
        'is_active' => 1,
        'translations' => ['vi' => ['name' => 'Danh mục audit', 'description' => '']],
    ])->assertRedirect('/admin/categories');

    expect(AuditLog::count())->toBeGreaterThan(0);
});

it('shows the dashboard', function () {
    $this->get('/admin')->assertStatus(200)->assertSee('Tổng quan');
});

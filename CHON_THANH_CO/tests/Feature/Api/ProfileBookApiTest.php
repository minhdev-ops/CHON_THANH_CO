<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('profile-book get endpoint returns 404 when file does not exist', function () {
    $response = $this->getJson('/api/v1/profile-book');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'url' => null,
        ]);
});

test('profile-book get endpoint returns 200 and url when file exists', function () {
    Storage::disk('public')->put('profile/ho-so-nang-luc.pdf', 'fake-pdf-content');

    $response = $this->getJson('/api/v1/profile-book');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('url'))->not->toBeNull();
});

test('unauthenticated users cannot upload profile book', function () {
    $file = UploadedFile::fake()->create('profile.pdf', 500, 'application/pdf');

    $response = $this->postJson('/api/v1/profile-book/upload', [
        'file' => $file,
    ]);

    $response->assertStatus(401);
});

test('authenticated admin can upload profile book pdf', function () {
    $file = UploadedFile::fake()->create('profile.pdf', 500, 'application/pdf');

    $response = $this->withSession([
        'admin_authenticated' => true,
        'admin_name' => 'admin',
    ])->postJson('/api/v1/profile-book/upload', [
        'file' => $file,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Upload hồ sơ năng lực thành công.',
        ]);

    Storage::disk('public')->assertExists('profile/ho-so-nang-luc.pdf');
});

test('upload profile book rejects non-pdf files', function () {
    $file = UploadedFile::fake()->create('script.php', 100, 'application/x-php');

    $response = $this->withSession([
        'admin_authenticated' => true,
        'admin_name' => 'admin',
    ])->postJson('/api/v1/profile-book/upload', [
        'file' => $file,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

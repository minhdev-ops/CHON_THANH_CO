<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Storage::fake không kế thừa config url của disk — truyền thẳng url vào fake.
    Storage::fake('s3', ['url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

it('returns 404 when no profile book exists', function () {
    $this->getJson('/api/v1/profile-book')->assertStatus(404);
});

it('uploads a pdf profile book to s3 and returns its public url', function () {
    $file = UploadedFile::fake()->create('hosonangluc.pdf', 500, 'application/pdf');

    $this->withSession(['admin_authenticated' => true, 'admin_name' => 'admin'])
        ->postJson('/api/v1/profile-book/upload', ['file' => $file])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('url', 'https://example.supabase.co/storage/v1/object/public/CHON_THANH/profile/ho-so-nang-luc.pdf');

    Storage::disk('s3')->assertExists('profile/ho-so-nang-luc.pdf');
});

it('serves the uploaded profile book url from show', function () {
    Storage::disk('s3')->put('profile/ho-so-nang-luc.pdf', 'pdf-bytes');

    $this->getJson('/api/v1/profile-book')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('url', 'https://example.supabase.co/storage/v1/object/public/CHON_THANH/profile/ho-so-nang-luc.pdf');
});

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
});

it('browses the images root folder', function () {
    $this->getJson('/admin/files/browse?type=Images&folder=')
        ->assertOk()
        ->assertJson(['type' => 'Images', 'folder' => ''])
        ->assertJsonStructure(['folders', 'files']);
});

it('browses a specific folder', function () {
    $this->getJson('/admin/files/browse?type=Images&folder=products')
        ->assertOk()
        ->assertJson(['type' => 'Images', 'folder' => 'products']);
});

it('rejects path traversal in browse', function () {
    $this->getJson('/admin/files/browse?type=Images&folder=../../etc')
        ->assertStatus(422)
        ->assertJson(['error' => 'Thư mục không hợp lệ.']);
});

it('uploads an image file', function () {
    $file = UploadedFile::fake()->image('test-image.png');

    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertOk()->assertJson(['success' => true]);

    // File phải tồn tại trên đĩa
    $files = glob(public_path('userfiles/images/products/test-image-*.png'));
    expect($files)->not->toBeEmpty();

    // Dọn dẹp
    foreach ($files as $f) {
        unlink($f);
    }
});

it('uploads a pdf file to the Files resource type', function () {
    $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

    $this->postJson('/admin/files/upload', [
        'type' => 'Files',
        'folder' => 'documents',
        'file' => $file,
    ])->assertOk()->assertJson(['success' => true]);

    $files = glob(public_path('userfiles/files/documents/brochure-*.pdf'));
    expect($files)->not->toBeEmpty();

    foreach ($files as $f) {
        unlink($f);
    }
});

it('rejects pdf upload to the Images resource type', function () {
    $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertStatus(422)
        ->assertJsonPath('error', 'Định dạng file không được phép (jpg, jpeg, png, gif, webp, bmp).');
});

it('rejects dangerous file extensions', function () {
    $file = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');

    $this->postJson('/admin/files/upload', [
        'type' => 'Files',
        'folder' => 'documents',
        'file' => $file,
    ])->assertStatus(422);
});

it('creates a folder', function () {
    $this->postJson('/admin/files/create-folder', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'test-folder-xyz',
    ])->assertOk()->assertJson(['success' => true]);

    expect(is_dir(public_path('userfiles/images/products/test-folder-xyz')))->toBeTrue();
    rmdir(public_path('userfiles/images/products/test-folder-xyz'));
});

it('renames a file', function () {
    $path = public_path('userfiles/images/products/rename-me.png');
    file_put_contents($path, 'fake-image-bytes');

    $this->postJson('/admin/files/rename', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'rename-me.png',
        'new_name' => 'renamed.png',
    ])->assertOk()->assertJson(['success' => true]);

    expect(file_exists(public_path('userfiles/images/products/renamed.png')))->toBeTrue();
    unlink(public_path('userfiles/images/products/renamed.png'));
});

it('rejects renaming a file to a dangerous extension', function () {
    $path = public_path('userfiles/images/products/evil.png');
    file_put_contents($path, 'fake');

    $this->postJson('/admin/files/rename', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'evil.png',
        'new_name' => 'evil.php',
    ])->assertStatus(422);

    unlink($path);
});

it('deletes a file', function () {
    $path = public_path('userfiles/images/products/to-delete.png');
    file_put_contents($path, 'fake');

    $this->deleteJson('/admin/files/delete', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'to-delete.png',
    ])->assertOk()->assertJson(['success' => true]);

    expect(file_exists($path))->toBeFalse();
});

it('rejects deleting with an empty name', function () {
    $this->deleteJson('/admin/files/delete', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => '',
    ])->assertStatus(422);
});

it('opens the picker with a folder', function () {
    $this->get('/admin/files/picker?input=image&type=Images&folder=products')
        ->assertStatus(200);
});

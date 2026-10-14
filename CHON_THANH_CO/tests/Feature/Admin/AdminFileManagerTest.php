<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
    Storage::fake('s3');
});

it('browses the images root folder', function () {
    Storage::disk('s3')->put('images/products/a.jpg', 'x');

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

it('uploads an image file to s3', function () {
    $file = UploadedFile::fake()->image('test-image.png');

    $response = $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertOk()->assertJson(['success' => true]);

    $name = $response->json('file.name');
    expect($name)->toStartWith('test-image-')
        ->and(Storage::disk('s3')->exists('images/products/'.$name))->toBeTrue()
        ->and($response->json('file.url'))->toContain('/images/products/'.$name);
});

it('uploads a pdf file to the Files resource type', function () {
    $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

    $response = $this->postJson('/admin/files/upload', [
        'type' => 'Files',
        'folder' => 'documents',
        'file' => $file,
    ])->assertOk()->assertJson(['success' => true]);

    $name = $response->json('file.name');
    expect(Storage::disk('s3')->exists('files/documents/'.$name))->toBeTrue();
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
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertStatus(422);
});

it('rejects oversized uploads', function () {
    $file = UploadedFile::fake()->create('big.jpg', 25 * 1024, 'image/jpeg');

    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertStatus(422)
        ->assertJson(['error' => 'File vượt quá dung lượng tối đa 20MB.']);
});

it('renames a file via copy and delete on s3', function () {
    Storage::disk('s3')->put('images/products/old-name.jpg', 'content');

    $this->postJson('/admin/files/rename', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'old-name.jpg',
        'new_name' => 'new-name.jpg',
    ])->assertOk()->assertJson(['success' => true]);

    Storage::disk('s3')->assertExists('images/products/new-name.jpg');
    Storage::disk('s3')->assertMissing('images/products/old-name.jpg');
});

it('rejects renaming a file to a dangerous extension', function () {
    Storage::disk('s3')->put('images/products/evil.png', 'fake');

    $this->postJson('/admin/files/rename', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'evil.png',
        'new_name' => 'evil.php',
    ])->assertStatus(422);
});

it('creates a folder as a placeholder object', function () {
    $this->postJson('/admin/files/create-folder', [
        'type' => 'Images',
        'folder' => '',
        'name' => 'Dự án mới',
    ])->assertOk()->assertJson(['success' => true, 'name' => 'du-an-moi']);

    Storage::disk('s3')->assertExists('images/du-an-moi/.keep');
});

it('destroys a single file', function () {
    Storage::disk('s3')->put('images/products/gone.jpg', 'x');

    $this->deleteJson('/admin/files/delete', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'gone.jpg',
    ])->assertOk()->assertJson(['success' => true]);

    Storage::disk('s3')->assertMissing('images/products/gone.jpg');
});

it('destroys a folder and all objects under its prefix', function () {
    Storage::disk('s3')->put('images/trash/a.jpg', 'x');
    Storage::disk('s3')->put('images/trash/b.jpg', 'x');

    $this->deleteJson('/admin/files/delete', [
        'type' => 'Images',
        'folder' => '',
        'name' => 'trash',
    ])->assertOk();

    Storage::disk('s3')->assertMissing('images/trash/a.jpg');
    Storage::disk('s3')->assertMissing('images/trash/b.jpg');
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

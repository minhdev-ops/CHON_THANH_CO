<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
    config()->set('filesystems.disks.s3.key', 'test-key');
    config()->set('filesystems.disks.s3.secret', 'test-secret');
    config()->set('filesystems.disks.s3.bucket', 'test-bucket');
    config()->set('filesystems.disks.s3.region', 'us-east-1');
    config()->set('filesystems.disks.s3.endpoint', 'https://s3.test.example.com');
    config()->set('filesystems.disks.s3.use_path_style_endpoint', true);
    Storage::fake('s3', ['url' => 'https://cdn.test.example.com/test-bucket']);
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

it('returns a presigned url that uploads an image directly to s3', function () {
    $response = $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'filename' => 'test-image.png',
        'size' => 1024,
        'content_type' => 'image/png',
    ])->assertOk()->assertJson(['success' => true]);

    $name = $response->json('file.name');
    $uploadUrl = $response->json('upload_url');

    expect($name)->toStartWith('test-image-')
        ->and($response->json('file.key'))->toBe('images/products/'.$name)
        ->and($response->json('file.url'))->toContain('cdn.test.example.com/test-bucket/images/products/'.$name)
        ->and($response->json('file.content_type'))->toBe('image/png')
        ->and($uploadUrl)->not->toBeEmpty()
        ->and(str_contains($uploadUrl, 's3.test.example.com/test-bucket/images/products/'.$name))->toBeTrue()
        ->and(str_contains($uploadUrl, 'X-Amz-Signature'))->toBeTrue();

    // Mô phỏng trình duyệt tải file lên trực tiếp lên S3 qua URL đã ký
    Http::fake(['*' => Http::response('', 200)]);

    Http::withHeaders([
        'x-amz-acl' => 'public-read',
        'Content-Type' => 'image/png',
    ])->put($uploadUrl, 'image-bytes');

    Http::assertSent(function ($request) use ($name) {
        return $request->method() === 'PUT'
            && str_contains($request->url(), $name);
    });
});

it('returns a presigned url for a pdf file in the Files resource type', function () {
    $response = $this->postJson('/admin/files/upload', [
        'type' => 'Files',
        'folder' => 'documents',
        'filename' => 'brochure.pdf',
        'size' => 100,
        'content_type' => 'application/pdf',
    ])->assertOk()->assertJson(['success' => true]);

    $name = $response->json('file.name');
    expect($response->json('file.key'))->toBe('files/documents/'.$name)
        ->and($response->json('file.url'))->toContain('cdn.test.example.com/test-bucket/files/documents/'.$name)
        ->and($response->json('upload_url'))->toContain($name);
});

it('rejects pdf upload to the Images resource type', function () {
    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'filename' => 'brochure.pdf',
        'size' => 100,
        'content_type' => 'application/pdf',
    ])->assertStatus(422)
        ->assertJsonPath('error', 'Định dạng file không được phép (jpg, jpeg, png, gif, webp, bmp).');
});

it('rejects dangerous file extensions', function () {
    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'filename' => 'shell.php',
        'size' => 100,
        'content_type' => 'application/x-php',
    ])->assertStatus(422);
});

it('rejects oversized uploads', function () {
    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'filename' => 'big.jpg',
        'size' => 25 * 1024 * 1024,
        'content_type' => 'image/jpeg',
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

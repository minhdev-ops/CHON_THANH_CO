<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('validates email format for contact form', function () {
    postJson('/api/v1/contact', [
        'name' => 'Nguyễn Văn A',
        'phone' => '0912345678',
        'email' => 'khong-phai-email',
        'message' => 'Xin báo giá.',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects a name containing digits', function () {
    postJson('/api/v1/contact', [
        'name' => 'Nguyễn 123',
        'phone' => '0912345678',
        'email' => 'a@example.com',
        'message' => 'Xin báo giá.',
    ])->assertStatus(422)->assertJsonValidationErrors('name');
});

it('rejects a phone number shorter than 9 digits', function () {
    postJson('/api/v1/contact', [
        'name' => 'Nguyễn Văn A',
        'phone' => '09123',
        'email' => 'a@example.com',
        'message' => 'Xin báo giá.',
    ])->assertStatus(422)->assertJsonValidationErrors('phone');
});

it('rejects duplicate selected products', function () {
    postJson('/api/v1/contact', [
        'name' => 'Nguyễn Văn A',
        'phone' => '0912345678',
        'email' => 'a@example.com',
        'message' => 'Xin báo giá.',
        'products' => ['Vải không dệt', 'Vải không dệt'],
    ])->assertStatus(422)->assertJsonValidationErrors('products.1');
});

it('accepts a contact form with multiple selected products', function () {
    postJson('/api/v1/contact', [
        'name' => 'Trần Thị B',
        'phone' => '0987654321',
        'email' => 'b@example.com',
        'company' => 'Công ty XYZ',
        'message' => 'Yêu cầu tư vấn mua lưới địa kỹ thuật.',
        'products' => ['Lưới địa kỹ thuật', 'Vải địa kỹ thuật'],
    ])->assertStatus(201);

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Trần Thị B',
        'email' => 'b@example.com',
        'status' => 'new',
    ]);
});

it('is a polite requirement: message must be at least 5 characters', function () {
    postJson('/api/v1/contact', [
        'name' => 'Nguyễn Văn A',
        'phone' => '0912345678',
        'email' => 'a@example.com',
        'message' => 'abc',
    ])->assertStatus(422)->assertJsonValidationErrors('message');
});
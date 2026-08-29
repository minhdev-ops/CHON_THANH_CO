<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('can submit a contact form successfully', function () {
    $payload = [
        'name' => 'Nguyễn Văn A',
        'phone' => '0912345678',
        'email' => 'nguyenvana@example.com',
        'company' => 'Công ty ABC',
        'product' => 'Vải Địa Kỹ Thuật Dệt',
        'message' => 'Xin báo giá thi công dự án đường bộ.',
    ];

    postJson('/api/v1/contact', $payload)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.',
        ])
        ->assertJsonStructure(['data' => ['id']]);

    assertDatabaseHas('contact_messages', [
        'name' => 'Nguyễn Văn A',
        'email' => 'nguyenvana@example.com',
        'status' => 'new',
    ]);
});

it('validates required fields for contact form', function () {
    postJson('/api/v1/contact', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'phone', 'email', 'message']);
});

<?php

use App\Models\Faq;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns faqs ordered by sort_order', function () {
    seed(FaqSeeder::class);

    getJson('/api/v1/faqs')
        ->assertStatus(200)
        ->assertJsonCount(7, 'data')
        ->assertJsonStructure(['data' => ['*' => ['question', 'answer']]])
        ->assertJsonPath('data.0.question', 'CHON THANH cung cấp những loại vật liệu địa kỹ thuật nào?')
        ->assertJsonPath('data.6.question', 'Các sản phẩm của CHON THANH có bảo hành không?');
});

it('serves faqs in english', function () {
    seed(FaqSeeder::class);

    getJson('/api/v1/faqs', ['X-Locale' => 'en'])
        ->assertStatus(200)
        ->assertJsonPath('data.0.question', 'What types of geosynthetic materials does CHON THANH supply?');
});

it('excludes inactive faqs', function () {
    seed(FaqSeeder::class);

    Faq::where('sort_order', 2)->update(['is_active' => false]);

    getJson('/api/v1/faqs')
        ->assertStatus(200)
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.1.question', 'Thời gian giao hàng trung bình là bao lâu?');
});

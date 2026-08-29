<?php

use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

it('returns global settings data', function () {
    seed(SettingSeeder::class);

    getJson('/api/v1/settings')
        ->assertStatus(200)
        ->assertJson([
            'company.name_vi' => 'CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH',
            'company.phone' => '0909 292 530',
            'contact.email' => 'chonthanhco@gmail.com',
        ]);
});

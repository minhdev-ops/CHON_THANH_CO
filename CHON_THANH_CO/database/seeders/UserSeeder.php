<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@chonthanh.vn'],
            [
                'name' => config('admin.username', 'Administrator'),
                'password' => Hash::make(config('admin.password', 'admin12345')),
            ]
        );
    }
}

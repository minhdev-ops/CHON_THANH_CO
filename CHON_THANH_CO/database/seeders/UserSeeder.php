<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = config('admin.password');
        if (blank($adminPassword) || in_array($adminPassword, ['password', 'admin12345'], true)) {
            throw new \RuntimeException('A strong ADMIN_PASSWORD must be configured before seeding.');
        }

        User::firstOrCreate(
            ['email' => 'admin@chonthanh.vn'],
            [
                'name' => config('admin.username', 'Administrator'),
                'password' => Hash::make($adminPassword),
            ]
        );
    }
}

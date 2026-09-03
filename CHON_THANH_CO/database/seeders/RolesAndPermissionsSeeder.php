<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $editor = Role::firstOrCreate(['name' => 'Editor']);

        // Create a default admin user if none exists
        $user = User::firstOrCreate(
            ['email' => 'admin@chonthanh.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Change this in production
            ]
        );

        $user->assignRole($superAdmin);
    }
}

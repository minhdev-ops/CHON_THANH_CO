<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Run our seeder to create roles
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('has a super admin role assigned to the default admin user', function () {
    $admin = User::where('email', 'admin@chonthanh.com')->first();
    
    expect($admin)->not->toBeNull();
    expect($admin->hasRole('Super Admin'))->toBeTrue();
});

it('has an editor role available', function () {
    $role = Role::where('name', 'Editor')->first();
    
    expect($role)->not->toBeNull();
});

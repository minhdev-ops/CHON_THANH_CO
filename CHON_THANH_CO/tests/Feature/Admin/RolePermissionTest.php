<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure Spatie permissions are reset
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Run our seeder to create roles
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_has_proper_role()
    {
        $admin = User::where('email', 'admin@chonthanh.com')->first();
        
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Super Admin'));
    }

    public function test_editor_role_exists()
    {
        $role = Role::where('name', 'Editor')->first();
        $this->assertNotNull($role);
    }
}

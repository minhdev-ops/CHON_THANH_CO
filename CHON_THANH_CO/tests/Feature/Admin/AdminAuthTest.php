<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests away from admin pages', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/products')->assertRedirect('/admin/login');
    $this->get('/admin/files')->assertRedirect('/admin/login');
    $this->get('/admin/settings')->assertRedirect('/admin/login');
});

it('shows the login page', function () {
    $this->get('/admin/login')->assertStatus(200)->assertSee('Đăng nhập');
});

it('allows admin to login with correct credentials', function () {
    $this->post('/admin/login', [
        'username' => config('admin.username'),
        'password' => config('admin.password'),
    ])->assertRedirect('/admin');

    $this->assertTrue(session()->has('admin_authenticated'));
    $this->get('/admin')->assertStatus(200);
});

it('rejects login with wrong password', function () {
    $this->post('/admin/login', [
        'username' => config('admin.username'),
        'password' => 'wrong-password-123',
    ])->assertSessionHasErrors('username');

    $this->assertFalse(session()->has('admin_authenticated'));
});

it('validates login fields are required', function () {
    $this->post('/admin/login', [])->assertSessionHasErrors(['username', 'password']);
});

it('logs the admin out', function () {
    $this->withSession(['admin_authenticated' => true]);

    $this->post('/admin/logout')->assertRedirect('/admin/login');

    $this->get('/admin')->assertRedirect('/admin/login');
});

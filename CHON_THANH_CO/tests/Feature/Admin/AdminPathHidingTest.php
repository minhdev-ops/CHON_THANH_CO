<?php

it('returns 404 on the old /admin path when ADMIN_PATH is customized', function () {
    $_ENV['ADMIN_PATH'] = 'ctl-secret-xyz';
    $_SERVER['ADMIN_PATH'] = 'ctl-secret-xyz';
    putenv('ADMIN_PATH=ctl-secret-xyz');

    $this->refreshApplication();

    $this->get('/admin')->assertStatus(404);
    $this->get('/admin/products')->assertStatus(404);
    $this->get('/admin/login')->assertStatus(404);

    // Admin vẫn truy cập được qua đường dẫn mới
    $this->get('/ctl-secret-xyz/login')
        ->assertStatus(200)
        ->assertSee('Đăng nhập');
});

it('defaults to /admin when ADMIN_PATH is not set', function () {
    $_ENV['ADMIN_PATH'] = '';
    $_SERVER['ADMIN_PATH'] = '';
    putenv('ADMIN_PATH=');

    $this->refreshApplication();

    $this->get('/admin/login')->assertStatus(200);
});

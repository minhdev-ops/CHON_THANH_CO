<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Đường dẫn quản trị (bí mật)
    |--------------------------------------------------------------------------
    |
    | Cấu hình qua biến môi trường ADMIN_PATH để ẩn trang quản trị khỏi người
    | ngoài. Khi đổi, đường dẫn /admin cũ sẽ trả về 404.
    |
    | Mặc định: 'admin'
    | Khuyến nghị cho production: một chuỗi ngẫu nhiên, dài và khó đoán,
    | ví dụ: ADMIN_PATH=ctl-mt9x-7k2w-hz5q
    |
    */

    // Lưu ý: nếu ADMIN_PATH rỗng sẽ fallback về 'admin' để tránh admin mount ở root
    'path' => trim((string) env('ADMIN_PATH')) ?: 'admin',

    /*
    |--------------------------------------------------------------------------
    | Thông tin đăng nhập quản trị
    |--------------------------------------------------------------------------
    */

    'username' => env('ADMIN_USERNAME', 'admin'),

    'password' => env('ADMIN_PASSWORD', 'admin12345'),
];

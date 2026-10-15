<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            'HOCK Technology',
            'ARITEX',
            'Petrovietnam',
            'Vingroup',
            'Coteccons',
            'Vinaconex',
            'Hoà Bình',
            'Delta',
            'Trung Nam',
            'CIENCO 4',
            'CIENCO 6',
            'Sông Đà',
        ];

        foreach ($partners as $index => $name) {
            Partner::updateOrCreate(
                ['name' => $name],
                [
                    // Logo sẽ được upload sau qua trang quản trị /admin/partners.
                    // Để null để dải đối tác dùng phần chữ thay vì hiện sai logo.
                    'image' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}

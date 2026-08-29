<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'code',
            'slug',
            'category',
            'name_vi',
            'name_en',
            'description_vi',
            'description_en',
            'strength_label_vi',
            'strength_label_en',
            'strength_min',
            'strength_max',
            'image',
            'applications',
            'is_featured',
            'is_active',
            'sort_order',
        ];
    }

    public function array(): array
    {
        return [
            [
                'ART 30',
                'art-30',
                'vai-kt-khong-det',
                'Vải địa kỹ thuật ARITEX ART 30',
                'ARITEX Geotextile ART 30',
                'Vải địa kỹ thuật không dệt, cường độ cao, dùng cho nền đường.',
                'Non-woven geotextile, high strength, used for road base.',
                'Cường độ 30 kN/m',
                'Strength 30 kN/m',
                '30',
                '30',
                '/images/products/art/art-30.jpg',
                'gia-co-nen, phan-cach-loc',
                '1',
                '1',
                '1',
            ],
        ];
    }
}

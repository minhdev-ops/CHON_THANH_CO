<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with(['translations', 'category.translations', 'applications.translations'])
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();
    }

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

    public function map($product): array
    {
        $vi = $product->translation('vi');
        $en = $product->translation('en');

        return [
            $product->code,
            $product->slug,
            $product->category?->slug ?? '',
            $vi?->name ?? '',
            $en?->name ?? '',
            $vi?->description ?? '',
            $en?->description ?? '',
            $vi?->strength_label ?? '',
            $en?->strength_label ?? '',
            $product->strength_min,
            $product->strength_max,
            $product->image,
            $product->applications?->pluck('slug')->implode(', ') ?? '',
            $product->is_featured ? 1 : 0,
            $product->is_active ? 1 : 0,
            $product->sort_order,
        ];
    }
}

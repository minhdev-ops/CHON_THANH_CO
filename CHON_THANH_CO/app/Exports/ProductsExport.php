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
        return Product::with(['category', 'applications'])
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
            'name',
            'description',
            'strength_label',
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
        return [
            $product->code,
            $product->slug,
            $product->category?->slug ?? '',
            $product->name,
            $product->description ?? '',
            $product->strength_label ?? '',
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
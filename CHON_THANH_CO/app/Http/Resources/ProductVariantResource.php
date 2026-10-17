<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Tóm tắt một biến thể của dòng sản phẩm.
 * Dùng chung cho bảng "các cấp trong dòng" và danh sách sản phẩm liên quan.
 */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'code' => $this->code,
            'name' => $this->name,
            'image' => MediaUrl::url($this->image),
            'strength_label' => $this->strength_label,
            'category' => $this->whenLoaded('category') && $this->category !== null
                ? ['slug' => $this->category->slug, 'name' => $this->category->name]
                : null,
        ];
    }
}

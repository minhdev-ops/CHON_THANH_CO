<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'detail' => $this->detail,
            'image' => $this->image,
            'product' => $this->whenLoaded('product', fn () => [
                'slug' => $this->product->slug,
                'code' => $this->product->code,
            ]),
        ];
    }
}
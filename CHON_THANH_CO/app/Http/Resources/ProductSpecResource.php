<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSpecResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'icon' => $this->icon,
            'label' => $this->translation()?->label,
            'value' => $this->value,
        ];
    }
}

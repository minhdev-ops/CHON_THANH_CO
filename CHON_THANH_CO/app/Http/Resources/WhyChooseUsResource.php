<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhyChooseUsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translation();

        return [
            'title' => $translation?->title,
            'description' => $translation?->description,
            'icon' => $this->icon,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translation();

        return [
            'section' => $this->section,
            'image' => $this->image,
            'link_to' => $this->link_to,
            'title' => $translation?->title,
            'subtitle' => $translation?->subtitle,
            'text' => $translation?->text,
            'button_text' => $translation?->button_text,
        ];
    }
}

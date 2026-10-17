<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'section' => $this->section,
            'image' => MediaUrl::url($this->image),
            'link_to' => $this->link_to,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'text' => $this->text,
            'button_text' => $this->button_text,
        ];
    }
}

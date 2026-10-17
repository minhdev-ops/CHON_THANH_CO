<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'image' => MediaUrl::url($this->image),
            'alt' => $this->alt,
        ];
    }
}

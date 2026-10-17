<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Tóm tắt công trình dùng sản phẩm, cho lưới "Công trình đã sử dụng". */
class ProjectSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'location' => $this->location,
            'period' => $this->period,
            'area' => $this->area,
            'hero_image' => MediaUrl::url($this->hero_image),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = $request->route('slug') !== null;

        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'location' => $this->location,
            'period' => $this->period,
            'area' => $this->area,
            'hero_image' => MediaUrl::url($this->hero_image),
            'desc_image' => MediaUrl::url($this->desc_image),
            $this->mergeWhen($detail, [
                'description' => $this->description,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'materials' => ProjectMaterialResource::collection($this->whenLoaded('materials')),
                'gallery' => ProjectImageResource::collection($this->whenLoaded('images')),
            ]),
        ];
    }
}

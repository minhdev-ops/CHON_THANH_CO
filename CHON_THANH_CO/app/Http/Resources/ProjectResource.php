<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translation();
        $detail = $request->route('slug') !== null;

        return [
            'slug' => $this->slug,
            'name' => $translation?->name,
            'location' => $translation?->location,
            'period' => $this->period,
            'area' => $this->area,
            'hero_image' => $this->hero_image,
            'desc_image' => $this->desc_image,
            $this->mergeWhen($detail, [
                'description' => $translation?->description,
                'materials' => ProjectMaterialResource::collection($this->whenLoaded('materials')),
                'gallery' => ProjectImageResource::collection($this->whenLoaded('images')),
            ]),
        ];
    }
}

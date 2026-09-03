<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translation();
        $detail = $request->route('slug') !== null;

        return [
            'slug' => $this->slug,
            'code' => $this->code,
            'name' => $translation?->name,
            'image' => $this->image,
            'strength_label' => $translation?->strength_label,
            'strength_min' => $this->strength_min,
            'strength_max' => $this->strength_max,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'applications' => ApplicationResource::collection($this->whenLoaded('applications')),
            $this->mergeWhen($detail, [
                'description' => $translation?->description,
                'specs' => ProductSpecResource::collection($this->whenLoaded('specs')),
                'images' => ProductImageResource::collection($this->whenLoaded('images')),
                'documents' => $this->whenLoaded('documents'),
            ]),
        ];
    }
}

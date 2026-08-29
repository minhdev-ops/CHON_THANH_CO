<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translation();

        $detail = $request->route('slug') !== null;

        return [
            'slug' => $this->slug,
            'title' => $translation?->title,
            'excerpt' => $translation?->excerpt,
            'image' => $this->image,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->whenLoaded('category') && $this->category !== null
                ? [
                    'slug' => $this->category->slug,
                    'name' => $this->category->translation()?->name,
                ]
                : null,
            $this->mergeWhen($detail, [
                'content' => $translation?->content,
            ]),
        ];
    }
}

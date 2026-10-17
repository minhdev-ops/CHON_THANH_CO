<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = $request->route('slug') !== null;

        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'image' => MediaUrl::url($this->image),
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->whenLoaded('category') && $this->category !== null
                ? [
                    'slug' => $this->category->slug,
                    'name' => $this->category->name,
                ]
                : null,
            $this->mergeWhen($detail, [
                'content' => $this->content,
            ]),
        ];
    }
}

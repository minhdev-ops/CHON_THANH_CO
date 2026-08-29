<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->translation();

        return [
            'slug' => $this->slug,
            'name' => $translation?->name,
            'description' => $translation?->description,
            'image' => $this->image,
            'file' => $this->file,
        ];
    }
}

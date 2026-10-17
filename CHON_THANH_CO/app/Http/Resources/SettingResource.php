<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    private const FILE_KEYS = ['company.capability_file', 'company.inspection_file'];

    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => in_array($this->key, self::FILE_KEYS, true)
                ? MediaUrl::url($this->value)
                : $this->value,
            'group' => $this->group,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BannerTranslation;

class Banner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'section',
        'title',
        'subtitle',
        'text',
        'button_text',
        'image',
        'link_to',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BannerTranslation::class);
    }
}


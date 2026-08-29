<?php

namespace App\Models;

use App\Models\Concerns\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsCategory extends Model
{
    use HasFactory;
    use Localizable;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(NewsCategoryTranslation::class);
    }
}

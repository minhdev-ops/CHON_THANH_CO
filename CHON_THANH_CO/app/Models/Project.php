<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProjectTranslation;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'location',
        'description',
        'period',
        'area',
        'hero_image',
        'desc_image',
        'is_featured',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }
}


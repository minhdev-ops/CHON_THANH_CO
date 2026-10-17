<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'parent_id',
        'slug',
        'code',
        'name',
        'description',
        'functions',
        'applications_text',
        'strength_label',
        'meta_title',
        'meta_description',
        'image',
        'strength_min',
        'strength_max',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'strength_min' => 'decimal:2',
            'strength_max' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Sản phẩm cha đại diện cho cả dòng (ví dụ dòng ART).
     * Sản phẩm độc lập thì parent_id = null.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Các mã cùng dòng, không gồm chính nó. */
    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** id của sản phẩm cha, tự trả về chính nó khi không có cha. */
    public function lineId(): int
    {
        return $this->parent_id ?? $this->id;
    }

    /** Các cấp cường độ của dòng (ART 7/9/12 kN/m...), nhập trong admin. */
    public function strengthGrades(): HasMany
    {
        return $this->hasMany(ProductStrengthGrade::class)->orderBy('sort_order');
    }

    public function projectMaterials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    /** Công trình đã dùng sản phẩm này. */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_materials',
            'product_id',
            'project_id'
        )->withTimestamps();
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'product_application');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class)->orderBy('sort_order');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'slug' => $this->slug,
            'category' => $this->category?->name,
            'parent_id' => $this->parent_id,
            'strength_min' => $this->strength_min,
            'strength_max' => $this->strength_max,
            'is_active' => $this->is_active,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }
}

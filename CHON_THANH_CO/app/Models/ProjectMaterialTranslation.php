<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMaterialTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'project_material_id',
        'locale',
        'name',
        'detail',
    ];

    public function projectMaterial(): BelongsTo
    {
        return $this->belongsTo(ProjectMaterial::class);
    }
}

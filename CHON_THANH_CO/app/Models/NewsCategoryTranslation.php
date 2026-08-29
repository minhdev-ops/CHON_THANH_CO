<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsCategoryTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'news_category_id',
        'locale',
        'name',
    ];

    public function newsCategory(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class);
    }
}

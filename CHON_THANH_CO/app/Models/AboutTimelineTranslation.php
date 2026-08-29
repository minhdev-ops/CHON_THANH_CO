<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutTimelineTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'about_timeline_id',
        'locale',
        'year',
        'description',
    ];

    public function aboutTimeline(): BelongsTo
    {
        return $this->belongsTo(AboutTimeline::class);
    }
}

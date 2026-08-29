<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeStatTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'home_stat_id',
        'locale',
        'label',
    ];

    public function homeStat(): BelongsTo
    {
        return $this->belongsTo(HomeStat::class);
    }
}

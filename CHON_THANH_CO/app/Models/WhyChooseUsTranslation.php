<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhyChooseUsTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'why_choose_us_id',
        'locale',
        'title',
        'description',
    ];

    public function whyChooseUs(): BelongsTo
    {
        return $this->belongsTo(WhyChooseUs::class);
    }
}

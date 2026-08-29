<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_spec_id',
        'locale',
        'label',
    ];

    public function productSpec(): BelongsTo
    {
        return $this->belongsTo(ProductSpec::class);
    }
}

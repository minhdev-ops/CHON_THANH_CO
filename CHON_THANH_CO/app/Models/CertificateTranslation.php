<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'certificate_id',
        'locale',
        'name',
        'description',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }
}

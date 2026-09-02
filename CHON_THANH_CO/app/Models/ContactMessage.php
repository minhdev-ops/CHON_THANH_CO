<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company',
        'product',
        'products',
        'message',
        'internal_note',
        'status',
        'handled_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
            'products' => 'array',
        ];
    }

    public function markHandled(): void
    {
        $this->update([
            'status' => 'replied',
            'handled_at' => now(),
        ]);
    }
}

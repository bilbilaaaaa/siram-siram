<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    protected $table = 'jadwal';

    protected $fillable = [
        'perangkat_id',
        'jam',
        'durasi_menit',
        'hari',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'durasi_menit' => 'integer',
            'hari' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
}

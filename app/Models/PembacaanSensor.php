<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembacaanSensor extends Model
{
    protected $table = 'pembacaan_sensor';

    public $timestamps = false;

    protected $fillable = [
        'perangkat_id',
        'kelembapan_tanah',
        'suhu',
        'kelembapan_udara',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'kelembapan_tanah' => 'integer',
            'suhu' => 'decimal:1',
            'kelembapan_udara' => 'integer',
            'dibaca_pada' => 'datetime',
        ];
    }

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
}

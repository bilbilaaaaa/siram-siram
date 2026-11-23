<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'perangkat_id',
        'mode',
        'pompa_manual',
        'batas_kering',
        'batas_basah',
    ];

    protected function casts(): array
    {
        return [
            'pompa_manual' => 'boolean',
            'batas_kering' => 'integer',
            'batas_basah' => 'integer',
        ];
    }

    public function statusKelembapanTanah(?int $kelembapan): ?string
    {
        if ($kelembapan === null) {
            return null;
        }

        if ($kelembapan < $this->batas_kering) {
            return 'Kering';
        }

        if ($kelembapan >= $this->batas_basah) {
            return 'Basah';
        }

        return 'Normal';
    }

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
}

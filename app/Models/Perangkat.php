<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Perangkat extends Model
{
    protected $table = 'perangkat';

    protected $fillable = [
        'nama',
        'ip_address',
        'nama_wifi',
        'terakhir_terhubung',
        'simulasi',
    ];

    protected function casts(): array
    {
        return [
            'terakhir_terhubung' => 'datetime',
            'simulasi' => 'boolean',
        ];
    }

    public function pembacaanSensor(): HasMany
    {
        return $this->hasMany(PembacaanSensor::class);
    }

    public function pengaturanSistem(): HasOne
    {
        return $this->hasOne(PengaturanSistem::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function riwayatPenyiraman(): HasMany
    {
        return $this->hasMany(RiwayatPenyiraman::class);
    }
}

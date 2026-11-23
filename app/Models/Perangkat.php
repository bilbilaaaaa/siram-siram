<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Perangkat extends Model
{
    public const KONEKSI_TERHUBUNG_DETIK = 60;

    public const KONEKSI_MENGHUBUNGKAN_DETIK = 180;

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

    public function statusKoneksi(): string
    {
        if ($this->terakhir_terhubung === null) {
            return 'terputus';
        }

        $detikSejakTerhubung = now()->getTimestamp() - $this->terakhir_terhubung->getTimestamp();

        if ($detikSejakTerhubung < self::KONEKSI_TERHUBUNG_DETIK) {
            return 'terhubung';
        }

        if ($detikSejakTerhubung <= self::KONEKSI_MENGHUBUNGKAN_DETIK) {
            return 'menghubungkan';
        }

        return 'terputus';
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

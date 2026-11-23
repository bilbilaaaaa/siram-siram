<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function waktuMulaiBerikutnya(?Carbon $sekarang = null): ?Carbon
    {
        if (! $this->aktif) {
            return null;
        }

        $hariTerpilih = $this->hari;

        if (! is_array($hariTerpilih) || $hariTerpilih === []) {
            return null;
        }

        $sekarang = ($sekarang ?? now())->copy();
        $hariIni = $sekarang->copy()->startOfDay();

        for ($selisihHari = 0; $selisihHari <= 7; $selisihHari++) {
            $tanggal = $hariIni->copy()->addDays($selisihHari);

            if (! in_array($tanggal->isoWeekday(), $hariTerpilih, true)) {
                continue;
            }

            $waktuMulai = $tanggal->setTimeFromTimeString($this->jam);

            if ($waktuMulai->greaterThan($sekarang)) {
                return $waktuMulai;
            }
        }

        return null;
    }

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }
}

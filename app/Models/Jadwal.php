<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    private const MENIT_PER_HARI = 1440;

    private const MENIT_PER_MINGGU = 10080;

    private const SINGKATAN_HARI = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

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

    /**
     * Temukan jadwal aktif yang waktunya beririsan pada hari yang sama.
     *
     * @param  array<int, int>  $hari
     */
    public static function cariBentrok(
        int $perangkatId,
        string $jam,
        int $durasiMenit,
        array $hari,
        ?int $kecualiId = null,
    ): ?self {
        $rentangBaru = self::rentangMingguan($jam, $durasiMenit, $hari);

        $jadwalAktif = self::query()
            ->where('perangkat_id', $perangkatId)
            ->where('aktif', true)
            ->when($kecualiId !== null, fn ($query) => $query->where('id', '!=', $kecualiId))
            ->orderBy('jam')
            ->get();

        foreach ($jadwalAktif as $jadwalLain) {
            $rentangLama = self::rentangMingguan(
                $jadwalLain->jam,
                $jadwalLain->durasi_menit,
                $jadwalLain->hari ?? [],
            );

            foreach ($rentangBaru as [$mulaiBaru, $selesaiBaru]) {
                foreach ($rentangLama as [$mulaiLama, $selesaiLama]) {
                    if ($mulaiBaru < $selesaiLama && $mulaiLama < $selesaiBaru) {
                        return $jadwalLain;
                    }
                }
            }
        }

        return null;
    }

    public function labelHari(): string
    {
        $hari = $this->hari ?? [];

        if (count(array_intersect(range(1, 7), $hari)) === 7) {
            return 'Setiap hari';
        }

        return implode(', ', array_map(
            fn (int $nomorHari): string => self::SINGKATAN_HARI[$nomorHari - 1],
            $hari,
        ));
    }

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class);
    }

    /**
     * @param  array<int, int>  $hari
     * @return array<int, array{0: int, 1: int}>
     */
    private static function rentangMingguan(string $jam, int $durasiMenit, array $hari): array
    {
        $jamDanMenit = array_map('intval', explode(':', substr($jam, 0, 5)));
        $menitMulaiHari = ($jamDanMenit[0] * 60) + $jamDanMenit[1];
        $rentang = [];

        foreach ($hari as $nomorHari) {
            $mulai = (($nomorHari - 1) * self::MENIT_PER_HARI) + $menitMulaiHari;
            $selesai = $mulai + $durasiMenit;

            if ($selesai <= self::MENIT_PER_MINGGU) {
                $rentang[] = [$mulai, $selesai];

                continue;
            }

            $rentang[] = [$mulai, self::MENIT_PER_MINGGU];
            $rentang[] = [0, $selesai - self::MENIT_PER_MINGGU];
        }

        return $rentang;
    }
}

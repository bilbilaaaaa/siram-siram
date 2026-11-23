<?php

namespace App\Http\Controllers;

use App\Models\PembacaanSensor;
use App\Models\Perangkat;
use App\Models\RiwayatPenyiraman;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class StatistikController extends Controller
{
    public function index(): View
    {
        $perangkat = Perangkat::firstOrFail();
        $sekarang = now();
        $awalHariIni = $sekarang->copy()->startOfDay();
        $awalTujuhHari = $sekarang->copy()->subDays(6)->startOfDay();
        $driver = (new PembacaanSensor)->getConnection()->getDriverName();
        $jamExpression = $driver === 'sqlite'
            ? "CAST(strftime('%H', dibaca_pada) AS INTEGER)"
            : 'HOUR(dibaca_pada)';

        $pembacaanPerJam = $perangkat->pembacaanSensor()
            ->whereBetween('dibaca_pada', [$awalHariIni, $sekarang])
            ->selectRaw(
                "$jamExpression AS periode, AVG(kelembapan_tanah) AS rata_kelembapan_tanah, ".
                'AVG(suhu) AS rata_suhu, AVG(kelembapan_udara) AS rata_kelembapan_udara, '.
                'COUNT(*) AS jumlah, MIN(kelembapan_tanah) AS tanah_min, '.
                'MAX(kelembapan_tanah) AS tanah_max, MIN(suhu) AS suhu_min, MAX(suhu) AS suhu_max',
            )
            ->groupByRaw($jamExpression)
            ->get()
            ->keyBy('periode');

        $labelJam = [];
        $kelembapanPerJam = [];
        $suhuPerJam = [];
        $kelembapanUdaraPerJam = [];

        foreach (range(0, 23) as $jam) {
            $labelJam[] = sprintf('%02d.00', $jam);
            $pembacaanJamIni = $pembacaanPerJam->get($jam);
            $kelembapanPerJam[] = $pembacaanJamIni === null
                ? null
                : round((float) $pembacaanJamIni->rata_kelembapan_tanah, 1);
            $suhuPerJam[] = $pembacaanJamIni === null
                ? null
                : round((float) $pembacaanJamIni->rata_suhu, 1);
            $kelembapanUdaraPerJam[] = $pembacaanJamIni === null
                ? null
                : round((float) $pembacaanJamIni->rata_kelembapan_udara, 1);
        }

        $jumlahPembacaanHariIni = (int) $pembacaanPerJam->sum('jumlah');
        $totalKelembapanHariIni = $pembacaanPerJam->sum(
            fn ($row): float => (float) $row->rata_kelembapan_tanah * (int) $row->jumlah,
        );
        $rataKelembapanHariIni = $jumlahPembacaanHariIni === 0
            ? null
            : round($totalKelembapanHariIni / $jumlahPembacaanHariIni, 1);
        $suhuMinimumHariIni = $pembacaanPerJam->min('suhu_min');
        $suhuMaksimumHariIni = $pembacaanPerJam->max('suhu_max');
        $kelembapanMinimumHariIni = $pembacaanPerJam->min('tanah_min');
        $kelembapanMaksimumHariIni = $pembacaanPerJam->max('tanah_max');

        $riwayatHariIni = $perangkat->riwayatPenyiraman()
            ->whereBetween('mulai_pada', [$awalHariIni, $sekarang])
            ->orderBy('mulai_pada')
            ->get(['mulai_pada', 'selesai_pada'])
            ->map(fn (RiwayatPenyiraman $riwayat): array => [
                'time' => $riwayat->mulai_pada->format('H.i'),
                'duration' => $this->durasiPenyiraman($riwayat, $sekarang),
            ])
            ->all();

        $labelTanggal = 'D, j M Y';
        $pembacaanPerHari = $perangkat->pembacaanSensor()
            ->whereBetween('dibaca_pada', [$awalTujuhHari, $sekarang])
            ->selectRaw(
                'DATE(dibaca_pada) AS periode, AVG(kelembapan_tanah) AS rata_kelembapan_tanah, '.
                'AVG(suhu) AS rata_suhu, AVG(kelembapan_udara) AS rata_kelembapan_udara, '.
                'COUNT(*) AS jumlah, MIN(kelembapan_tanah) AS tanah_min, '.
                'MAX(kelembapan_tanah) AS tanah_max, MIN(suhu) AS suhu_min, MAX(suhu) AS suhu_max',
            )
            ->groupByRaw('DATE(dibaca_pada)')
            ->get()
            ->keyBy('periode');

        $dailyWaterDuration = $driver === 'sqlite'
            ? 'COALESCE(SUM(CASE WHEN selesai_pada IS NOT NULL THEN '.
                'CAST((julianday(selesai_pada) - julianday(mulai_pada)) * 1440 AS INTEGER) ELSE 0 END), 0)'
            : 'COALESCE(SUM(CASE WHEN selesai_pada IS NOT NULL THEN '.
                'TIMESTAMPDIFF(MINUTE, mulai_pada, selesai_pada) ELSE 0 END), 0)';
        $penyiramanPerHari = $perangkat->riwayatPenyiraman()
            ->whereBetween('mulai_pada', [$awalTujuhHari, $sekarang])
            ->selectRaw(
                "DATE(mulai_pada) AS periode, COUNT(*) AS jumlah, {$dailyWaterDuration} AS total_menit",
            )
            ->groupByRaw('DATE(mulai_pada)')
            ->get()
            ->keyBy('periode');

        $labelTujuhHari = [];
        $kelembapanPerHari = [];
        $suhuPerHari = [];
        $kelembapanUdaraPerHari = [];
        $jumlahPembacaanTujuhHari = 0;
        $totalKelembapanTujuhHari = 0;
        $suhuMinimumTujuhHari = null;
        $suhuMaksimumTujuhHari = null;
        $kelembapanMinimumTujuhHari = null;
        $kelembapanMaksimumTujuhHari = null;
        $totalPenyiramanPerHari = [];
        $jumlahPenyiramanPerHari = [];

        for ($selisihHari = 6; $selisihHari >= 0; $selisihHari--) {
            $tanggal = $sekarang->copy()->startOfDay()->subDays($selisihHari);
            $tanggalKey = $tanggal->toDateString();
            $pembacaanHariIni = $pembacaanPerHari->get($tanggalKey);
            $penyiramanHariIni = $penyiramanPerHari->get($tanggalKey);
            $labelTujuhHari[] = $tanggal->locale('id')->translatedFormat('D');

            $kelembapanPerHari[] = $pembacaanHariIni === null
                ? null
                : round((float) $pembacaanHariIni->rata_kelembapan_tanah, 1);
            $suhuPerHari[] = $pembacaanHariIni === null
                ? null
                : round((float) $pembacaanHariIni->rata_suhu, 1);
            $kelembapanUdaraPerHari[] = $pembacaanHariIni === null
                ? null
                : round((float) $pembacaanHariIni->rata_kelembapan_udara, 1);
            $totalPenyiramanPerHari[] = $penyiramanHariIni === null
                ? 0
                : (int) round((float) $penyiramanHariIni->total_menit);
            $jumlahPenyiramanPerHari[] = $penyiramanHariIni === null
                ? 0
                : (int) $penyiramanHariIni->jumlah;

            if ($pembacaanHariIni !== null) {
                $jumlah = (int) $pembacaanHariIni->jumlah;
                $jumlahPembacaanTujuhHari += $jumlah;
                $totalKelembapanTujuhHari += (float) $pembacaanHariIni->rata_kelembapan_tanah * $jumlah;
                $suhuMinimumTujuhHari = min($suhuMinimumTujuhHari ?? PHP_FLOAT_MAX, (float) $pembacaanHariIni->suhu_min);
                $suhuMaksimumTujuhHari = max($suhuMaksimumTujuhHari ?? -PHP_FLOAT_MAX, (float) $pembacaanHariIni->suhu_max);
                $kelembapanMinimumTujuhHari = min($kelembapanMinimumTujuhHari ?? PHP_FLOAT_MAX, (float) $pembacaanHariIni->tanah_min);
                $kelembapanMaksimumTujuhHari = max($kelembapanMaksimumTujuhHari ?? -PHP_FLOAT_MAX, (float) $pembacaanHariIni->tanah_max);
            }
        }

        $riwayatTujuhHari = $perangkat->riwayatPenyiraman()
            ->whereBetween('mulai_pada', [$awalTujuhHari, $sekarang])
            ->orderBy('mulai_pada')
            ->get(['mulai_pada', 'selesai_pada'])
            ->map(fn (RiwayatPenyiraman $riwayat): array => [
                'time' => $riwayat->mulai_pada->locale('id')->translatedFormat('j M H.i'),
                'duration' => $this->durasiPenyiraman($riwayat, $sekarang),
            ])
            ->all();

        $rataKelembapanTujuhHari = $jumlahPembacaanTujuhHari === 0
            ? null
            : round($totalKelembapanTujuhHari / $jumlahPembacaanTujuhHari, 1);

        $statistik = [
            'periods' => [
                'hari_ini' => [
                    'label' => $sekarang->copy()->locale('id')->translatedFormat($labelTanggal).' • 00.00–'.$sekarang->format('H.i'),
                    'soil_moisture' => [
                        'labels' => $labelJam,
                        'values' => $kelembapanPerJam,
                    ],
                    'air_humidity' => [
                        'labels' => $labelJam,
                        'values' => $kelembapanUdaraPerJam,
                    ],
                    'temperature' => [
                        'labels' => $labelJam,
                        'values' => $suhuPerJam,
                    ],
                    'watering_history' => $riwayatHariIni,
                    'watering_total_minutes' => array_sum(array_column($riwayatHariIni, 'duration')),
                    'watering_count' => count($riwayatHariIni),
                    'average_soil_moisture' => $rataKelembapanHariIni,
                    'minimum_temperature' => $suhuMinimumHariIni === null ? null : round((float) $suhuMinimumHariIni, 1),
                    'maximum_temperature' => $suhuMaksimumHariIni === null ? null : round((float) $suhuMaksimumHariIni, 1),
                    'soil_moisture_range' => $this->formatRentang(
                        $kelembapanMinimumHariIni === null ? null : (float) $kelembapanMinimumHariIni,
                        $kelembapanMaksimumHariIni === null ? null : (float) $kelembapanMaksimumHariIni,
                    ),
                    'reading_count' => $jumlahPembacaanHariIni,
                ],
                'tujuh_hari' => [
                    'label' => $awalTujuhHari->copy()->locale('id')->translatedFormat('j M').'–'.$sekarang->copy()->locale('id')->translatedFormat('j M Y'),
                    'soil_moisture' => [
                        'labels' => $labelTujuhHari,
                        'values' => $kelembapanPerHari,
                    ],
                    'air_humidity' => [
                        'labels' => $labelTujuhHari,
                        'values' => $kelembapanUdaraPerHari,
                    ],
                    'temperature' => [
                        'labels' => $labelTujuhHari,
                        'values' => $suhuPerHari,
                    ],
                    'watering_history' => $riwayatTujuhHari,
                    'watering_total_minutes' => [
                        'labels' => $labelTujuhHari,
                        'values' => $totalPenyiramanPerHari,
                    ],
                    'watering_count' => [
                        'labels' => $labelTujuhHari,
                        'values' => $jumlahPenyiramanPerHari,
                    ],
                    'average_soil_moisture' => $rataKelembapanTujuhHari,
                    'minimum_temperature' => $suhuMinimumTujuhHari === null ? null : round($suhuMinimumTujuhHari, 1),
                    'maximum_temperature' => $suhuMaksimumTujuhHari === null ? null : round($suhuMaksimumTujuhHari, 1),
                    'soil_moisture_range' => $this->formatRentang(
                        $kelembapanMinimumTujuhHari === null ? null : $kelembapanMinimumTujuhHari,
                        $kelembapanMaksimumTujuhHari === null ? null : $kelembapanMaksimumTujuhHari,
                    ),
                    'reading_count' => $jumlahPembacaanTujuhHari,
                ],
            ],
        ];

        return view('pages.statistik', compact('statistik'));
    }

    private function durasiPenyiraman(RiwayatPenyiraman $riwayat, Carbon $sekarang): int
    {
        return max(0, (int) $riwayat->mulai_pada->diffInMinutes($riwayat->selesai_pada ?? $sekarang));
    }

    private function formatRentang(?float $minimum, ?float $maksimum): string
    {
        if ($minimum === null || $maksimum === null) {
            return '—';
        }

        return number_format($minimum, 1, '.', '').'–'.number_format($maksimum, 1, '.', '').'%';
    }
}

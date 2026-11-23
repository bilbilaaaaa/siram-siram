<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $perangkat = Perangkat::firstOrFail();
        $pengaturan = $perangkat->pengaturanSistem()->firstOrFail();
        $pembacaanTerbaru = $perangkat->pembacaanSensor()
            ->latest('dibaca_pada')
            ->take(5)
            ->get();
        $pembacaanTerakhir = $pembacaanTerbaru->first();
        $kelembapanTanah = $pembacaanTerbaru->isEmpty()
            ? null
            : (int) round($pembacaanTerbaru->avg('kelembapan_tanah'));

        $jadwalBerikutnya = $perangkat->jadwal()
            ->where('aktif', true)
            ->get()
            ->map(fn ($jadwal): array => [
                'jadwal' => $jadwal,
                'waktu_mulai' => $jadwal->waktuMulaiBerikutnya(),
            ])
            ->filter(fn (array $kandidat): bool => $kandidat['waktu_mulai'] !== null)
            ->sortBy(fn (array $kandidat): int => $kandidat['waktu_mulai']->getTimestamp())
            ->first();

        $statusKoneksiPerangkat = $perangkat->statusKoneksi();
        $connectionStatus = $statusKoneksiPerangkat;

        if (app()->environment('local')) {
            // This status override is only for local development.
            $statusPengembangan = $request->query('status');

            if (in_array($statusPengembangan, ['terhubung', 'menghubungkan', 'terputus'], true)) {
                $connectionStatus = $statusPengembangan;
            }
        }

        $sensor = [
            'soil_moisture' => $kelembapanTanah,
            'temperature' => $pembacaanTerakhir?->suhu,
            'air_humidity' => $pembacaanTerakhir?->kelembapan_udara,
        ];

        // Riwayat yang masih terbuka tidak berarti pompa menyala jika perangkat sudah terputus.
        $sedangMenyiram = $statusKoneksiPerangkat !== 'terputus'
            && $perangkat->riwayatPenyiraman()->whereNull('selesai_pada')->exists();

        $status = [
            'connection' => match ($connectionStatus) {
                'menghubungkan' => 'Menghubungkan ulang',
                'terputus' => 'Terputus',
                default => 'Terhubung',
            },
            'soil_moisture' => $pengaturan->statusKelembapanTanah($kelembapanTanah) ?? '—',
            'watering_schedule' => ucfirst($pengaturan->mode),
            'watering' => $sedangMenyiram ? 'Menyiram' : 'Tidak Menyiram',
            'next_watering_in' => $jadwalBerikutnya === null
                ? null
                : $this->formatJarakWaktu($jadwalBerikutnya['waktu_mulai']),
            'next_schedule_time' => $jadwalBerikutnya === null
                ? '—'
                : $jadwalBerikutnya['waktu_mulai']->format('H.i'),
            'next_schedule_duration' => $jadwalBerikutnya === null
                ? '—'
                : $jadwalBerikutnya['jadwal']->durasi_menit.' menit',
        ];

        $koneksi = ['status' => $connectionStatus];
        $terakhirDiperbarui = $pembacaanTerakhir?->dibaca_pada;

        return view('pages.dashboard', compact('sensor', 'status', 'koneksi', 'pengaturan', 'terakhirDiperbarui'));
    }

    private function formatJarakWaktu(Carbon $waktuMulai): string
    {
        $menit = max(1, (int) ceil(now()->diffInSeconds($waktuMulai) / 60));

        if ($menit >= 1440) {
            $hari = intdiv($menit, 1440);
            $jam = intdiv($menit % 1440, 60);

            return $jam > 0 ? "{$hari} hari {$jam} jam" : "{$hari} hari";
        }

        if ($menit >= 60) {
            $jam = intdiv($menit, 60);
            $sisaMenit = $menit % 60;

            return $sisaMenit > 0 ? "{$jam} jam {$sisaMenit} menit" : "{$jam} jam";
        }

        return "{$menit} menit";
    }
}

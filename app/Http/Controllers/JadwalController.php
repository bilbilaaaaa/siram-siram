<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Perangkat;
use Illuminate\Contracts\View\View;

class JadwalController extends Controller
{
    public function index(): View
    {
        $perangkat = Perangkat::firstOrFail();
        $jadwalModel = $perangkat->jadwal()->orderBy('jam')->get();
        $singkatanHari = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

        $jadwalBerikutnya = $jadwalModel
            ->map(fn (Jadwal $item): array => [
                'jadwal' => $item,
                'waktu_mulai' => $item->waktuMulaiBerikutnya(),
            ])
            ->filter(fn (array $kandidat): bool => $kandidat['waktu_mulai'] !== null)
            ->sortBy(fn (array $kandidat): int => $kandidat['waktu_mulai']->getTimestamp())
            ->first();

        $jadwal = $jadwalModel
            ->map(function (Jadwal $item) use ($singkatanHari): array {
                $hariTerpilih = $item->hari ?? [];
                $hari = count(array_diff(range(1, 7), $hariTerpilih)) === 0
                    ? 'Setiap hari'
                    : array_values(array_filter(array_map(
                        fn (int $nomorHari): ?string => $singkatanHari[$nomorHari - 1] ?? null,
                        $hariTerpilih,
                    )));

                return [
                    'id' => $item->id,
                    'jam' => substr($item->jam, 0, 5),
                    'hari' => $hari,
                    'durasi' => $item->durasi_menit,
                    'aktif' => $item->aktif,
                ];
            })
            ->all();

        $jadwalBerikutnya = $jadwalBerikutnya === null
            ? null
            : [
                'id' => $jadwalBerikutnya['jadwal']->id,
                'jam' => $jadwalBerikutnya['waktu_mulai']->format('H:i'),
                'durasi' => $jadwalBerikutnya['jadwal']->durasi_menit,
                'waktu_mulai' => $jadwalBerikutnya['waktu_mulai'],
            ];

        return view('pages.jadwal', compact('jadwal', 'jadwalBerikutnya'));
    }
}

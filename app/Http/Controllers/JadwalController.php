<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanJadwalRequest;
use App\Models\Jadwal;
use App\Models\Perangkat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

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
                    'hari' => $hariTerpilih,
                    'hari_label' => $hari,
                    'durasi' => $item->durasi_menit,
                    'aktif' => $item->aktif,
                    'url_ubah' => route('jadwal.update', $item),
                    'url_aktif' => route('jadwal.aktif', $item),
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

    public function store(SimpanJadwalRequest $request): RedirectResponse
    {
        $perangkat = Perangkat::firstOrFail();
        $data = $this->dataJadwal($request);
        $bentrok = Jadwal::cariBentrok(
            $perangkat->id,
            $data['jam'],
            $data['durasi_menit'],
            $data['hari'],
        );

        if ($bentrok !== null) {
            return redirect()->route('jadwal')
                ->withInput()
                ->with('error', $this->pesanBentrok($bentrok));
        }

        $perangkat->jadwal()->create($data);

        return redirect()->route('jadwal');
    }

    public function update(SimpanJadwalRequest $request, Jadwal $jadwal): RedirectResponse
    {
        $perangkat = Perangkat::firstOrFail();
        abort_unless($jadwal->perangkat_id === $perangkat->id, 404);

        $data = $this->dataJadwal($request);
        $bentrok = Jadwal::cariBentrok(
            $perangkat->id,
            $data['jam'],
            $data['durasi_menit'],
            $data['hari'],
            $jadwal->id,
        );

        if ($bentrok !== null) {
            return redirect()->route('jadwal')
                ->withInput()
                ->with('error', $this->pesanBentrok($bentrok));
        }

        $jadwal->update($data);

        return redirect()->route('jadwal');
    }

    public function toggleAktif(Jadwal $jadwal): JsonResponse
    {
        $perangkat = Perangkat::firstOrFail();
        abort_unless($jadwal->perangkat_id === $perangkat->id, 404);

        $aktifBaru = ! $jadwal->aktif;

        if ($aktifBaru) {
            $bentrok = Jadwal::cariBentrok(
                $perangkat->id,
                $jadwal->jam,
                $jadwal->durasi_menit,
                $jadwal->hari ?? [],
                $jadwal->id,
            );

            if ($bentrok !== null) {
                return response()->json([
                    'aktif' => $jadwal->aktif,
                    'pesan' => $this->pesanBentrok($bentrok),
                ], 422);
            }
        }

        $jadwal->update(['aktif' => $aktifBaru]);

        return response()->json([
            'aktif' => $jadwal->aktif,
            'pesan' => $aktifBaru ? 'Jadwal berhasil diaktifkan.' : 'Jadwal berhasil dinonaktifkan.',
        ]);
    }

    /**
     * @return array{jam: string, durasi_menit: int, hari: array<int, int>}
     */
    private function dataJadwal(SimpanJadwalRequest $request): array
    {
        $data = $request->validated();
        $hari = array_values(array_unique(array_map('intval', $data['hari'])));
        sort($hari);

        return [
            'jam' => $data['jam'],
            'durasi_menit' => (int) $data['durasi'],
            'hari' => $hari,
        ];
    }

    private function pesanBentrok(Jadwal $jadwal): string
    {
        return sprintf(
            'Bentrok dengan jadwal pukul %s pada %s.',
            substr($jadwal->jam, 0, 5),
            $jadwal->labelHari(),
        );
    }
}

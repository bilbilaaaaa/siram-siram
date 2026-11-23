<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Perangkat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SinkronPerangkatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kelembapan_tanah' => ['required', 'integer', 'between:0,100'],
            'suhu' => ['required', 'numeric', 'between:-10,70'],
            'kelembapan_udara' => ['required', 'integer', 'between:0,100'],
            'pompa_menyala' => ['required', 'boolean'],
            'pemicu' => [
                'nullable',
                Rule::requiredIf($request->boolean('pompa_menyala')),
                Rule::in(['otomatis', 'manual', 'terjadwal']),
            ],
            'jadwal_id' => ['nullable', Rule::exists((new Jadwal)->getTable(), 'id')],
            'ip_address' => ['nullable', 'ip'],
            'nama_wifi' => ['nullable', 'string', 'max:64'],
        ], [
            'kelembapan_tanah.required' => 'Kelembapan tanah wajib diisi.',
            'kelembapan_tanah.integer' => 'Kelembapan tanah harus berupa bilangan bulat.',
            'kelembapan_tanah.between' => 'Kelembapan tanah harus di antara 0 dan 100.',
            'suhu.required' => 'Suhu wajib diisi.',
            'suhu.numeric' => 'Suhu harus berupa angka.',
            'suhu.between' => 'Suhu harus di antara -10 dan 70.',
            'kelembapan_udara.required' => 'Kelembapan udara wajib diisi.',
            'kelembapan_udara.integer' => 'Kelembapan udara harus berupa bilangan bulat.',
            'kelembapan_udara.between' => 'Kelembapan udara harus di antara 0 dan 100.',
            'pompa_menyala.required' => 'Status pompa wajib diisi.',
            'pompa_menyala.boolean' => 'Status pompa harus berupa true atau false.',
            'pemicu.required' => 'Pemicu wajib diisi saat pompa menyala.',
            'pemicu.in' => 'Pemicu harus otomatis, manual, atau terjadwal.',
            'jadwal_id.exists' => 'Jadwal yang dipilih tidak ditemukan.',
            'ip_address.ip' => 'Alamat IP tidak valid.',
            'nama_wifi.string' => 'Nama Wi-Fi harus berupa teks.',
            'nama_wifi.max' => 'Nama Wi-Fi maksimal 64 karakter.',
        ], [
            'kelembapan_tanah' => 'kelembapan tanah',
            'kelembapan_udara' => 'kelembapan udara',
            'pompa_menyala' => 'status pompa',
            'jadwal_id' => 'jadwal',
            'ip_address' => 'alamat IP',
            'nama_wifi' => 'nama Wi-Fi',
        ]);

        $konfigurasi = DB::transaction(function () use ($data): array {
            $perangkat = Perangkat::query()->lockForUpdate()->firstOrFail();
            $waktuSekarang = now();

            $perangkat->pembacaanSensor()->create([
                'kelembapan_tanah' => $data['kelembapan_tanah'],
                'suhu' => $data['suhu'],
                'kelembapan_udara' => $data['kelembapan_udara'],
                'dibaca_pada' => $waktuSekarang,
            ]);

            $perubahanPerangkat = ['terakhir_terhubung' => $waktuSekarang];

            foreach (['ip_address', 'nama_wifi'] as $atribut) {
                if (array_key_exists($atribut, $data)) {
                    $perubahanPerangkat[$atribut] = $data[$atribut];
                }
            }

            $perangkat->update($perubahanPerangkat);

            $riwayatTerbuka = $perangkat->riwayatPenyiraman()
                ->whereNull('selesai_pada')
                ->lockForUpdate()
                ->first();

            if ($data['pompa_menyala'] && $riwayatTerbuka === null) {
                $perangkat->riwayatPenyiraman()->create([
                    'jadwal_id' => $data['jadwal_id'] ?? null,
                    'mulai_pada' => $waktuSekarang,
                    'selesai_pada' => null,
                    'pemicu' => $data['pemicu'],
                ]);
            } elseif (! $data['pompa_menyala'] && $riwayatTerbuka !== null) {
                $perangkat->riwayatPenyiraman()
                    ->whereNull('selesai_pada')
                    ->update(['selesai_pada' => $waktuSekarang]);
            }

            $pengaturan = $perangkat->pengaturanSistem()->firstOrFail();
            $jadwalAktif = $perangkat->jadwal()
                ->where('aktif', true)
                ->orderBy('jam')
                ->get(['id', 'jam', 'durasi_menit', 'hari'])
                ->map(fn (Jadwal $jadwal): array => [
                    'id' => $jadwal->id,
                    'jam' => substr($jadwal->jam, 0, 5),
                    'durasi_menit' => $jadwal->durasi_menit,
                    'hari' => $jadwal->hari,
                ])
                ->all();

            return [
                'waktu_server' => $waktuSekarang->toIso8601String(),
                'mode' => $pengaturan->mode,
                'pompa_manual' => $pengaturan->pompa_manual,
                'batas_kering' => $pengaturan->batas_kering,
                'batas_basah' => $pengaturan->batas_basah,
                'jadwal' => $jadwalAktif,
            ];
        });

        return response()->json($konfigurasi);
    }
}

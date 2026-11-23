<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Contracts\View\View;

class KontrolController extends Controller
{
    public function index(): View
    {
        $perangkat = Perangkat::firstOrFail();
        $pengaturan = $perangkat->pengaturanSistem()->firstOrFail();
        $pembacaanTerakhir = $perangkat->pembacaanSensor()
            ->latest('dibaca_pada')
            ->first();
        $kelembapanTanah = $pembacaanTerakhir?->kelembapan_tanah;

        return view('pages.kontrol', [
            'modeAktif' => $pengaturan->mode,
            'pompaManual' => $pengaturan->pompa_manual,
            'batasKering' => $pengaturan->batas_kering,
            'batasBasah' => $pengaturan->batas_basah,
            'kelembapanTanah' => $kelembapanTanah,
            'statusKelembapanTanah' => $pengaturan->statusKelembapanTanah($kelembapanTanah),
        ]);
    }
}

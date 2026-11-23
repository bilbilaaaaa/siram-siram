<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class KoneksiController extends Controller
{
    public function index(Request $request): View
    {
        $perangkat = Perangkat::firstOrFail();
        $status = $perangkat->statusKoneksi();

        if (app()->environment('local')) {
            // This status override is only for local development.
            $statusPengembangan = $request->query('status');

            if (in_array($statusPengembangan, ['terhubung', 'menghubungkan', 'terputus'], true)) {
                $status = $statusPengembangan;
            }
        }

        $stateContent = [
            'terhubung' => [
                'judul' => 'Terhubung',
                'keterangan' => 'ESP32 siap merawat tanaman Anda.',
                'info' => 'Ponsel dan ESP32 berada di jaringan Wi-Fi yang sama. Data sensor diperbarui otomatis.',
                'label_tombol' => 'Periksa Koneksi',
                'label_waktu' => 'Terakhir terhubung',
            ],
            'menghubungkan' => [
                'judul' => 'Menghubungkan Ulang',
                'keterangan' => 'Mencoba tersambung kembali ke ESP32...',
                'info' => 'Jangan tutup aplikasi. Perintah penyiraman ditunda hingga koneksi tersedia kembali.',
                'label_tombol' => 'Menghubungkan...',
                'label_waktu' => 'Koneksi terakhir',
            ],
            'terputus' => [
                'judul' => 'Terputus',
                'keterangan' => 'Koneksi ke perangkat ESP32 terhenti.',
                'info' => 'Tidak terhubung ke SiramSiram. Perintah tidak dapat dikirim.',
                'label_tombol' => 'Coba Hubungkan Lagi',
                'label_waktu' => 'Koneksi terakhir',
            ],
        ];

        $terakhirTerhubung = $perangkat->terakhir_terhubung;
        $terakhir = $terakhirTerhubung === null
            ? 'Belum pernah terhubung'
            : ($terakhirTerhubung->isToday()
                ? 'Hari ini, '.$terakhirTerhubung->locale('id')->translatedFormat('H.i')
                : $terakhirTerhubung->locale('id')->translatedFormat('D, j M Y · H.i'));

        $koneksi = [
            'status' => $status,
            'ip' => $perangkat->ip_address ?? '—',
            'wifi' => $perangkat->nama_wifi ?? '—',
            'perangkat' => $perangkat->nama,
            'terakhir' => $terakhir,
            ...$stateContent[$status],
        ];

        return view('pages.koneksi', compact('koneksi'));
    }
}

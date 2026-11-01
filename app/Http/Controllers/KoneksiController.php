<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class KoneksiController extends Controller
{
    public function index(Request $request): View
    {
        // Parameter status hanya untuk pengembangan dan akan dihapus.
        $status = $request->query('status', 'terhubung');
        if (! in_array($status, ['terhubung', 'menghubungkan', 'terputus'], true)) {
            $status = 'terhubung';
        }

        $stateContent = [
            'terhubung' => [
                'judul' => 'Terhubung',
                'keterangan' => 'ESP32 siap merawat tanaman Anda.',
                'info' => 'Ponsel dan ESP32 berada di jaringan Wi-Fi yang sama. Data sensor diperbarui otomatis.',
                'label_tombol' => 'Periksa Koneksi',
                'label_waktu' => 'Terakhir terhubung',
                'terakhir' => 'Hari ini, 07.40',
            ],
            'menghubungkan' => [
                'judul' => 'Menghubungkan Ulang',
                'keterangan' => 'Mencoba tersambung kembali ke ESP32...',
                'info' => 'Jangan tutup aplikasi. Perintah penyiraman ditunda hingga koneksi tersedia kembali.',
                'label_tombol' => 'Menghubungkan...',
                'label_waktu' => 'Koneksi terakhir',
                'terakhir' => 'Hari ini, 07.35',
            ],
            'terputus' => [
                'judul' => 'Terputus',
                'keterangan' => 'Koneksi ke perangkat ESP32 terhenti.',
                'info' => 'Tidak terhubung ke SiramSiram. Perintah tidak dapat dikirim.',
                'label_tombol' => 'Coba Hubungkan Lagi',
                'label_waktu' => 'Koneksi terakhir',
                'terakhir' => 'Hari ini, 07.35',
            ],
        ];

        $koneksi = [
            'status' => $status,
            'ip' => '192.168.1.120',
            'wifi' => 'KebunRumah',
            'perangkat' => 'ESP32 · Kebun TOGA',
            ...$stateContent[$status],
        ];

        return view('pages.koneksi', compact('koneksi'));
    }
}

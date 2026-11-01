<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Parameter status hanya untuk pengembangan dan akan dihapus.
        $connectionStatus = $request->query('status', 'terhubung');
        if (! in_array($connectionStatus, ['terhubung', 'menghubungkan', 'terputus'], true)) {
            $connectionStatus = 'terhubung';
        }

        $sensor = [
            'soil_moisture' => 50,
            'temperature' => 28,
            'air_humidity' => 70,
        ];

        $status = [
            'connection' => match ($connectionStatus) {
                'menghubungkan' => 'Menghubungkan ulang',
                'terputus' => 'Terputus',
                default => 'Terhubung',
            },
            'soil_moisture' => 'Normal',
            'watering_schedule' => 'Terjadwal',
            'watering' => 'Tidak Menyiram',
            'next_watering_in' => '20 menit',
            'next_schedule_time' => '08.00',
            'next_schedule_duration' => '2 menit',
        ];

        $koneksi = ['status' => $connectionStatus];

        return view('pages.dashboard', compact('sensor', 'status', 'koneksi'));
    }
}

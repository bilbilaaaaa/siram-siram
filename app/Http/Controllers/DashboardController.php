<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sensor = [
            'soil_moisture' => 50,
            'temperature' => 28,
            'air_humidity' => 70,
        ];

        $status = [
            'connection' => 'Terhubung',
            'soil_moisture' => 'Normal',
            'watering_schedule' => 'Terjadwal',
            'watering' => 'Tidak Menyiram',
            'next_watering_in' => '20 menit',
            'next_schedule_time' => '08.00',
            'next_schedule_duration' => '2 menit',
        ];

        return view('pages.dashboard', compact('sensor', 'status'));
    }
}

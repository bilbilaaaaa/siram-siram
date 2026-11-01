<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class JadwalController extends Controller
{
    public function index(): View
    {
        $jadwal = [
            [
                'jam' => '08:00',
                'hari' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                'durasi' => 2,
                'aktif' => true,
            ],
            [
                'jam' => '16:30',
                'hari' => ['Sen', 'Rab', 'Jum'],
                'durasi' => 3,
                'aktif' => true,
            ],
            [
                'jam' => '07:00',
                'hari' => ['Sab', 'Min'],
                'durasi' => 2,
                'aktif' => false,
            ],
        ];

        return view('pages.jadwal', compact('jadwal'));
    }
}

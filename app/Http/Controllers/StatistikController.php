<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StatistikController extends Controller
{
    public function index(): View
    {
        $statistik = [
            'periods' => [
                'hari_ini' => [
                    'label' => 'Sen, 2 Nov 2026 • 00.00–07.40',
                    'soil_moisture' => [
                        'labels' => ['00.00', '02.00', '04.00', '06.00', '07.40'],
                        'values' => [60, 65, 45, 55, 50],
                    ],
                    'air_humidity' => [
                        'labels' => ['00.00', '02.00', '04.00', '06.00', '07.40'],
                        'values' => [72, 74, 69, 71, 70],
                    ],
                    'temperature' => [
                        'labels' => ['00.00', '02.00', '04.00', '06.00', '07.40'],
                        'values' => [26, 26, 27, 28, 28],
                    ],
                    'watering_history' => [
                        ['time' => '06.00', 'duration' => 2],
                    ],
                    'average_soil_moisture' => 55,
                    'minimum_temperature' => 26,
                    'maximum_temperature' => 28,
                    'soil_moisture_range' => '45–65%',
                    'reading_count' => 5,
                ],
                'tujuh_hari' => [
                    'label' => '27 Okt–2 Nov 2026',
                    'soil_moisture' => [
                        'labels' => ['Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min', 'Sen'],
                        'values' => [52, 58, 49, 62, 55, 48, 55],
                    ],
                    'air_humidity' => [
                        'labels' => ['Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min', 'Sen'],
                        'values' => [70, 72, 68, 75, 73, 69, 70],
                    ],
                    'temperature' => [
                        'labels' => ['Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min', 'Sen'],
                        'values' => [26, 27, 27, 28, 27, 26, 28],
                    ],
                    'watering_history' => [
                        ['time' => '27 Okt 08.00', 'duration' => 2],
                        ['time' => '28 Okt 08.00', 'duration' => 2],
                        ['time' => '29 Okt 08.00', 'duration' => 2],
                        ['time' => '30 Okt 08.00', 'duration' => 2],
                        ['time' => '31 Okt 08.00', 'duration' => 2],
                        ['time' => '1 Nov 08.00', 'duration' => 2],
                        ['time' => '2 Nov 08.00', 'duration' => 2],
                    ],
                    'average_soil_moisture' => 54,
                    'minimum_temperature' => 26,
                    'maximum_temperature' => 28,
                    'soil_moisture_range' => '48–62%',
                    'reading_count' => 7,
                ],
            ],
        ];

        return view('pages.statistik', compact('statistik'));
    }
}

<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Perangkat;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perangkat = Perangkat::where('nama', 'ESP32 · Kebun TOGA')->firstOrFail();

        $jadwal = [
            [
                'jam' => '06:00:00',
                'durasi_menit' => 10,
                'hari' => [1, 2, 3, 4, 5, 6, 7],
                'aktif' => true,
            ],
            [
                'jam' => '17:00:00',
                'durasi_menit' => 8,
                'hari' => [1, 3, 5],
                'aktif' => true,
            ],
            [
                'jam' => '12:00:00',
                'durasi_menit' => 5,
                'hari' => [6],
                'aktif' => false,
            ],
        ];

        foreach ($jadwal as $data) {
            Jadwal::updateOrCreate(
                [
                    'perangkat_id' => $perangkat->id,
                    'jam' => $data['jam'],
                ],
                $data,
            );
        }
    }
}

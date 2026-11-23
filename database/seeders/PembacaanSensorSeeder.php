<?php

namespace Database\Seeders;

use App\Models\Perangkat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembacaanSensorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perangkat = Perangkat::where('nama', 'ESP32 · Kebun TOGA')->firstOrFail();
        $now = now();
        $start = $now->copy()->subDays(7);
        $rows = [];

        for ($index = 0; $index <= 336; $index++) {
            $dibacaPada = $start->copy()->addMinutes($index * 30);
            $hour = $dibacaPada->hour + ($dibacaPada->minute / 60);

            if ($hour >= 6 && $hour < 17) {
                $kelembapanTanah = 60 - (30 * (($hour - 6) / 11));
            } elseif ($hour >= 17 && $hour < 19) {
                $kelembapanTanah = 30 + (15 * ($hour - 17));
            } elseif ($hour >= 19) {
                $kelembapanTanah = 60 - (5 * (($hour - 19) / 5));
            } else {
                $kelembapanTanah = 55 + (5 * ($hour / 6));
            }

            $temperature = 29 + (5 * cos(2 * M_PI * ($hour - 14) / 24));
            $temperature = round(min(34, max(24, $temperature)), 1);
            $kelembapanUdara = (int) round(min(85, max(55, 70 - (($temperature - 29) * 2))));

            $rows[] = [
                'perangkat_id' => $perangkat->id,
                'kelembapan_tanah' => (int) round($kelembapanTanah),
                'suhu' => number_format($temperature, 1, '.', ''),
                'kelembapan_udara' => $kelembapanUdara,
                'dibaca_pada' => $dibacaPada->toDateTimeString(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('pembacaan_sensor')->insert($chunk);
        }
    }
}

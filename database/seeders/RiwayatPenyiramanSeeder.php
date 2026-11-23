<?php

namespace Database\Seeders;

use App\Models\Perangkat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiwayatPenyiramanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perangkat = Perangkat::where('nama', 'ESP32 · Kebun TOGA')->firstOrFail();
        $jadwalPagi = $perangkat->jadwal()->where('jam', '06:00:00')->firstOrFail();
        $jadwalSore = $perangkat->jadwal()->where('jam', '17:00:00')->firstOrFail();
        $now = now();
        $rows = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $day = $now->copy()->startOfDay()->subDays($daysAgo);
            $dayOfWeek = $day->isoWeekday();
            $events = [
                ['06:00:00', 10, 'terjadwal', $jadwalPagi->id],
            ];

            if (in_array($dayOfWeek, [1, 3, 5], true)) {
                $events[] = ['17:00:00', 8, 'terjadwal', $jadwalSore->id];
            } elseif (in_array($dayOfWeek, [2, 4], true)) {
                $events[] = ['17:30:00', 8, 'otomatis', null];
            } elseif ($dayOfWeek === 6) {
                $events[] = ['17:00:00', 8, 'otomatis', null];
            } elseif ($dayOfWeek === 7) {
                $events[] = ['17:00:00', 8, 'manual', null];
            }

            foreach ($events as [$time, $duration, $trigger, $jadwalId]) {
                $startedAt = $day->copy()->setTimeFromTimeString($time);

                if ($startedAt->greaterThan($now)) {
                    continue;
                }

                $finishedAt = $startedAt->copy()->addMinutes($duration);

                $rows[] = [
                    'perangkat_id' => $perangkat->id,
                    'jadwal_id' => $jadwalId,
                    'mulai_pada' => $startedAt->toDateTimeString(),
                    'selesai_pada' => $finishedAt->toDateTimeString(),
                    'pemicu' => $trigger,
                    'created_at' => $finishedAt->toDateTimeString(),
                    'updated_at' => $finishedAt->toDateTimeString(),
                ];
            }
        }

        usort($rows, fn (array $first, array $second): int => $first['mulai_pada'] <=> $second['mulai_pada']);

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('riwayat_penyiraman')->insert($chunk);
        }
    }
}

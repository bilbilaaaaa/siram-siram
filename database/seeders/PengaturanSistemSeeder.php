<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use App\Models\Perangkat;
use Illuminate\Database\Seeder;

class PengaturanSistemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perangkat = Perangkat::where('nama', 'ESP32 · Kebun TOGA')->firstOrFail();

        PengaturanSistem::updateOrCreate(
            ['perangkat_id' => $perangkat->id],
            [
                'mode' => 'terjadwal',
                'pompa_manual' => false,
                'batas_kering' => 30,
                'batas_basah' => 70,
            ],
        );
    }
}

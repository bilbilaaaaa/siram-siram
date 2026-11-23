<?php

namespace Database\Seeders;

use App\Models\Perangkat;
use Illuminate\Database\Seeder;

class PerangkatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Perangkat::updateOrCreate(
            ['nama' => 'ESP32 · Kebun TOGA'],
            [
                'ip_address' => '192.168.1.120',
                'nama_wifi' => 'KebunRumah',
                'simulasi' => true,
                'terakhir_terhubung' => now(),
            ],
        );
    }
}

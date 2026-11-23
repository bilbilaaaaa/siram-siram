<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\PembacaanSensor;
use App\Models\PengaturanSistem;
use App\Models\Perangkat;
use App\Models\RiwayatPenyiraman;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_does_not_show_watering_when_device_is_disconnected_with_open_history(): void
    {
        $this->travelTo('2026-11-23 14:00:00');
        RiwayatPenyiraman::query()->delete();
        Jadwal::query()->delete();
        PengaturanSistem::query()->delete();
        PembacaanSensor::query()->delete();
        Perangkat::query()->delete();

        $perangkat = Perangkat::create([
            'nama' => 'Perangkat Dashboard Test',
            'terakhir_terhubung' => now()->subMinutes(4),
            'simulasi' => true,
        ]);
        PengaturanSistem::create(['perangkat_id' => $perangkat->id]);
        $perangkat->riwayatPenyiraman()->create([
            'mulai_pada' => now()->subMinutes(10),
            'pemicu' => 'manual',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Terputus')
            ->assertSeeText('Tidak Menyiram');
    }
}

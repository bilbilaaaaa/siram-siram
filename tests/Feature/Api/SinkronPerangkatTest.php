<?php

namespace Tests\Feature\Api;

use App\Models\Jadwal;
use App\Models\PembacaanSensor;
use App\Models\PengaturanSistem;
use App\Models\Perangkat;
use App\Models\RiwayatPenyiraman;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SinkronPerangkatTest extends TestCase
{
    use DatabaseTransactions;

    private Perangkat $perangkat;

    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        config(['siram.device_token' => 'token-uji-perangkat']);
        $this->travelTo('2026-11-23 14:00:00');
        RiwayatPenyiraman::query()->delete();
        Jadwal::query()->delete();
        PengaturanSistem::query()->delete();
        PembacaanSensor::query()->delete();
        Perangkat::query()->delete();
        $this->perangkat = Perangkat::create([
            'nama' => 'Perangkat API Test',
            'simulasi' => true,
        ]);
        PengaturanSistem::create([
            'perangkat_id' => $this->perangkat->id,
            'mode' => 'terjadwal',
        ]);
        $this->jadwal = Jadwal::create([
            'perangkat_id' => $this->perangkat->id,
            'jam' => '06:00:00',
            'durasi_menit' => 10,
            'hari' => [1, 2, 3, 4, 5, 6, 7],
            'aktif' => true,
        ]);
    }

    public function test_rejects_missing_or_invalid_device_tokens(): void
    {
        config(['siram.device_token' => null]);

        $this->postJson('/api/perangkat/sinkron', [])
            ->assertUnauthorized()
            ->assertExactJson(['pesan' => 'Token tidak valid.']);

        config(['siram.device_token' => 'token-uji-perangkat']);

        $this->withToken('token-salah')
            ->postJson('/api/perangkat/sinkron', [])
            ->assertUnauthorized()
            ->assertExactJson(['pesan' => 'Token tidak valid.']);
    }

    public function test_stores_reading_and_changes_pump_history_only_when_pump_state_changes(): void
    {
        $payload = [
            'kelembapan_tanah' => 42,
            'suhu' => 28.5,
            'kelembapan_udara' => 70,
            'pompa_menyala' => true,
            'pemicu' => 'terjadwal',
            'jadwal_id' => $this->jadwal->id,
            'ip_address' => '192.168.1.120',
            'nama_wifi' => 'KebunRumah',
        ];

        $response = $this->withToken('token-uji-perangkat')
            ->postJson('/api/perangkat/sinkron', $payload);

        $response
            ->assertOk()
            ->assertJsonPath('waktu_server', '2026-11-23T14:00:00+07:00')
            ->assertJsonPath('mode', 'terjadwal')
            ->assertJsonPath('pompa_manual', false)
            ->assertJsonPath('batas_kering', 30)
            ->assertJsonPath('batas_basah', 70)
            ->assertJsonPath('jadwal.0.id', $this->jadwal->id)
            ->assertJsonPath('jadwal.0.jam', '06:00')
            ->assertJsonPath('jadwal.0.hari', [1, 2, 3, 4, 5, 6, 7]);

        $this->assertDatabaseHas('pembacaan_sensor', [
            'perangkat_id' => $this->perangkat->id,
            'kelembapan_tanah' => 42,
            'suhu' => '28.5',
            'kelembapan_udara' => 70,
        ]);
        $this->assertDatabaseHas('perangkat', [
            'id' => $this->perangkat->id,
            'ip_address' => '192.168.1.120',
            'nama_wifi' => 'KebunRumah',
            'terakhir_terhubung' => '2026-11-23 14:00:00',
        ]);
        $this->assertSame(1, $this->perangkat->riwayatPenyiraman()->count());

        $this->withToken('token-uji-perangkat')
            ->postJson('/api/perangkat/sinkron', $payload)
            ->assertOk();

        $this->assertSame(1, $this->perangkat->riwayatPenyiraman()->count());

        $payload['pompa_menyala'] = false;
        unset($payload['pemicu'], $payload['jadwal_id']);

        $this->withToken('token-uji-perangkat')
            ->postJson('/api/perangkat/sinkron', $payload)
            ->assertOk();

        $this->assertSame(1, $this->perangkat->riwayatPenyiraman()->whereNotNull('selesai_pada')->count());
    }

    public function test_returns_indonesian_validation_errors_and_does_not_store_invalid_readings(): void
    {
        $response = $this->withToken('token-uji-perangkat')
            ->postJson('/api/perangkat/sinkron', [
                'kelembapan_tanah' => 101,
                'suhu' => 71,
                'kelembapan_udara' => -1,
                'pompa_menyala' => true,
                'ip_address' => 'bukan-ip',
                'nama_wifi' => str_repeat('a', 65),
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'kelembapan_tanah',
                'suhu',
                'kelembapan_udara',
                'pemicu',
                'ip_address',
                'nama_wifi',
            ])
            ->assertJsonPath('errors.pemicu.0', 'Pemicu wajib diisi saat pompa menyala.');

        $this->assertSame(0, $this->perangkat->pembacaanSensor()->count());
    }
}

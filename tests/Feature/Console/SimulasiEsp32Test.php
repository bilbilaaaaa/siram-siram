<?php

namespace Tests\Feature\Console;

use App\Models\Jadwal;
use App\Models\PembacaanSensor;
use App\Models\PengaturanSistem;
use App\Models\Perangkat;
use App\Models\RiwayatPenyiraman;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SimulasiEsp32Test extends TestCase
{
    use DatabaseTransactions;

    public function test_sends_one_authenticated_sensor_reading_to_the_api(): void
    {
        RiwayatPenyiraman::query()->delete();
        Jadwal::query()->delete();
        PengaturanSistem::query()->delete();
        PembacaanSensor::query()->delete();
        Perangkat::query()->delete();

        $perangkat = Perangkat::create([
            'nama' => 'Perangkat Simulator Test',
            'simulasi' => true,
        ]);
        PengaturanSistem::create([
            'perangkat_id' => $perangkat->id,
            'mode' => 'otomatis',
        ]);
        config(['siram.device_token' => 'token-uji-simulator']);

        Http::preventStrayRequests();
        Http::fake([
            'simulator.test/api/perangkat/sinkron' => Http::response([
                'waktu_server' => now()->toIso8601String(),
                'mode' => 'manual',
                'pompa_manual' => true,
                'batas_kering' => 30,
                'batas_basah' => 70,
                'jadwal' => [],
            ]),
        ]);

        $this->artisan('sensor:simulasi', [
            '--url' => 'https://simulator.test',
            '--sekali' => true,
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://simulator.test/api/perangkat/sinkron'
            && $request->hasHeader('Authorization', 'Bearer token-uji-simulator')
            && $request->hasHeader('Accept', 'application/json')
            && is_int($request['kelembapan_tanah'])
            && is_numeric($request['suhu'])
            && is_int($request['kelembapan_udara'])
            && is_bool($request['pompa_menyala']));
    }

    public function test_reports_an_empty_device_token_without_sending_a_request(): void
    {
        config(['siram.device_token' => '']);
        Http::preventStrayRequests();

        $this->artisan('sensor:simulasi', ['--sekali' => true])
            ->expectsOutput('Token perangkat kosong. Isi SIRAM_DEVICE_TOKEN sebelum menjalankan simulator.')
            ->assertFailed();
    }

    public function test_displays_validation_messages_returned_by_the_api(): void
    {
        RiwayatPenyiraman::query()->delete();
        Jadwal::query()->delete();
        PengaturanSistem::query()->delete();
        PembacaanSensor::query()->delete();
        Perangkat::query()->delete();

        Perangkat::create([
            'nama' => 'Perangkat Simulator Test',
            'simulasi' => true,
        ]);
        config(['siram.device_token' => 'token-uji-simulator']);

        Http::preventStrayRequests();
        Http::fake([
            'simulator.test/api/perangkat/sinkron' => Http::response([
                'message' => 'Data tidak valid.',
                'errors' => [
                    'kelembapan_tanah' => ['Kelembapan tanah wajib diisi.'],
                ],
            ], 422),
        ]);

        $this->artisan('sensor:simulasi', [
            '--url' => 'https://simulator.test',
            '--sekali' => true,
        ])
            ->expectsOutput('Data ditolak (HTTP 422): Kelembapan tanah wajib diisi.')
            ->assertFailed();
    }
}

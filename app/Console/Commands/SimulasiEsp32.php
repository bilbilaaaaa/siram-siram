<?php

namespace App\Console\Commands;

use App\Models\Perangkat;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

#[Signature('sensor:simulasi {--interval=5} {--kecepatan=1} {--url=} {--sekali}')]
#[Description('Simulasikan pengiriman sensor ESP32 ke API')]
class SimulasiEsp32 extends Command
{
    public function handle(): int
    {
        // Simulasi ini sementara dan nantinya digantikan oleh ESP32 sungguhan.
        $interval = filter_var($this->option('interval'), FILTER_VALIDATE_INT);
        $kecepatan = filter_var($this->option('kecepatan'), FILTER_VALIDATE_FLOAT);

        if ($interval === false || $interval < 1) {
            $this->error('Interval harus berupa bilangan bulat minimal 1 detik.');

            return self::FAILURE;
        }

        if ($kecepatan === false || $kecepatan <= 0) {
            $this->error('Kecepatan harus berupa angka lebih besar dari 0.');

            return self::FAILURE;
        }

        $token = config('siram.device_token');

        if (! is_string($token) || trim($token) === '') {
            $this->error('Token perangkat kosong. Isi SIRAM_DEVICE_TOKEN sebelum menjalankan simulator.');

            return self::FAILURE;
        }

        $urlDasar = trim((string) $this->option('url'));
        $urlDasar = $urlDasar !== '' ? $urlDasar : (string) config('app.url');
        $urlApi = rtrim($urlDasar, '/').'/api/perangkat/sinkron';
        $skemaUrl = parse_url($urlApi, PHP_URL_SCHEME);

        if (! filter_var($urlApi, FILTER_VALIDATE_URL) || ! in_array($skemaUrl, ['http', 'https'], true)) {
            $this->error('URL API tidak valid. Gunakan URL HTTP atau HTTPS.');

            return self::FAILURE;
        }

        $perangkat = Perangkat::first();

        if ($perangkat === null) {
            $this->error('Data perangkat belum tersedia. Siapkan satu perangkat sebelum menjalankan simulator.');

            return self::FAILURE;
        }

        $pembacaanTerakhir = $perangkat->pembacaanSensor()
            ->latest('dibaca_pada')
            ->first();
        $kelembapanTanah = (float) ($pembacaanTerakhir?->kelembapan_tanah ?? 50);
        $konfigurasi = [
            'mode' => 'otomatis',
            'pompa_manual' => false,
            'batas_kering' => 30,
            'batas_basah' => 70,
            'jadwal' => [],
        ];
        $pompaMenyala = false;
        $waktuServer = now();
        $kirimSekali = (bool) $this->option('sekali');

        do {
            $waktuSiklus = $waktuServer->copy();
            $modeSiklus = $konfigurasi['mode'];
            $jamSimulasi = (float) $waktuSiklus->format('G') + ((float) $waktuSiklus->format('i') / 60);
            $suhu = 29 + (5 * cos(2 * M_PI * ($jamSimulasi - 13) / 24)) + (random_int(-5, 5) / 10);
            $suhu = round(min(34, max(24, $suhu)), 1);
            $kelembapanUdara = (int) round(min(85, max(55, 70 - (($suhu - 29) * 2))));
            $jadwalTerpilih = null;

            if ($konfigurasi['mode'] === 'otomatis') {
                if ($kelembapanTanah < $konfigurasi['batas_kering']) {
                    $pompaMenyala = true;
                } elseif ($kelembapanTanah >= (($konfigurasi['batas_kering'] + $konfigurasi['batas_basah']) / 2) + 5) {
                    $pompaMenyala = false;
                }
            } elseif ($konfigurasi['mode'] === 'manual') {
                $pompaMenyala = $konfigurasi['pompa_manual'];
            } else {
                $jadwalTerpilih = $this->jadwalYangSedangBerjalan($konfigurasi['jadwal'], $waktuSiklus);
                $pompaMenyala = $jadwalTerpilih !== null;
            }

            $perubahanTanah = $pompaMenyala ? 2 * $kecepatan : -0.15 * $kecepatan;
            $kelembapanTanah = round(min(100, max(0, $kelembapanTanah + $perubahanTanah)), 1);
            $payload = [
                'kelembapan_tanah' => (int) round($kelembapanTanah),
                'suhu' => $suhu,
                'kelembapan_udara' => $kelembapanUdara,
                'pompa_menyala' => $pompaMenyala,
                'pemicu' => $pompaMenyala ? $konfigurasi['mode'] : null,
                'jadwal_id' => $pompaMenyala && $konfigurasi['mode'] === 'terjadwal'
                    ? $jadwalTerpilih['id']
                    : null,
            ];

            $kodeHttp = 'GAGAL';
            $pesanGagal = null;
            $responsBerhasil = false;

            try {
                $respons = Http::withToken($token)
                    ->acceptJson()
                    ->connectTimeout(3)
                    ->timeout(5)
                    ->post($urlApi, $payload);

                $kodeHttp = (string) $respons->status();

                if ($respons->successful()) {
                    $responsBerhasil = true;
                    $isiRespons = $respons->json();

                    if (is_array($isiRespons) && $this->perbaruiKonfigurasi($isiRespons, $konfigurasi)) {
                        if (isset($isiRespons['waktu_server']) && is_string($isiRespons['waktu_server'])) {
                            $waktuServer = Carbon::parse($isiRespons['waktu_server']);
                        }
                    } else {
                        $pesanGagal = 'Respons berhasil, tetapi konfigurasi perangkat tidak lengkap.';
                    }
                } elseif ($respons->unauthorized()) {
                    $pesanGagal = 'Token ditolak (HTTP 401). Periksa SIRAM_DEVICE_TOKEN.';
                } elseif ($respons->unprocessableEntity()) {
                    $pesanGagal = 'Data ditolak (HTTP 422): '.$this->ringkasanKesalahan($respons->json('errors'));
                } else {
                    $pesanGagal = 'API mengembalikan HTTP '.$respons->status().'.';
                }
            } catch (ConnectionException) {
                $pesanGagal = 'Tidak dapat terhubung ke API perangkat. Periksa URL dan koneksi jaringan.';
            }

            $baris = sprintf(
                '%s | tanah %d%% | suhu %.1f°C | udara %d%% | pompa %s | mode %s | HTTP %s',
                $waktuSiklus->format('H:i:s'),
                $payload['kelembapan_tanah'],
                $payload['suhu'],
                $payload['kelembapan_udara'],
                $pompaMenyala ? 'menyala' : 'mati',
                $modeSiklus,
                $kodeHttp,
            );
            $this->line($baris);

            if ($pesanGagal !== null) {
                $this->warn($pesanGagal);
            }

            if ($kirimSekali) {
                return $responsBerhasil ? self::SUCCESS : self::FAILURE;
            }

            $waktuServer = $waktuServer->copy()->addSeconds($interval);
            sleep($interval);
        } while (true);
    }

    /**
     * @param  array<int, array{id: int, jam: string, durasi_menit: int, hari: array<int, int>}>  $jadwalAktif
     * @return array{id: int, jam: string, durasi_menit: int, hari: array<int, int>}|null
     */
    private function jadwalYangSedangBerjalan(array $jadwalAktif, Carbon $waktuServer): ?array
    {
        foreach ($jadwalAktif as $jadwal) {
            $hariMulaiTerakhir = (int) ceil($jadwal['durasi_menit'] / 1440);

            for ($hariLalu = 0; $hariLalu <= $hariMulaiTerakhir; $hariLalu++) {
                $waktuMulai = $waktuServer->copy()
                    ->startOfDay()
                    ->subDays($hariLalu)
                    ->setTimeFromTimeString($jadwal['jam']);

                if (! in_array($waktuMulai->isoWeekday(), $jadwal['hari'], true)) {
                    continue;
                }

                $waktuSelesai = $waktuMulai->copy()->addMinutes($jadwal['durasi_menit']);

                if ($waktuServer->greaterThanOrEqualTo($waktuMulai) && $waktuServer->lessThan($waktuSelesai)) {
                    return $jadwal;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $respons
     * @param  array{mode: string, pompa_manual: bool, batas_kering: int, batas_basah: int, jadwal: array}  $konfigurasi
     */
    private function perbaruiKonfigurasi(array $respons, array &$konfigurasi): bool
    {
        if (
            ! in_array($respons['mode'] ?? null, ['otomatis', 'manual', 'terjadwal'], true)
            || ! is_bool($respons['pompa_manual'] ?? null)
            || ! is_numeric($respons['batas_kering'] ?? null)
            || ! is_numeric($respons['batas_basah'] ?? null)
            || ! is_array($respons['jadwal'] ?? null)
        ) {
            return false;
        }

        $konfigurasi = [
            'mode' => $respons['mode'],
            'pompa_manual' => $respons['pompa_manual'],
            'batas_kering' => (int) $respons['batas_kering'],
            'batas_basah' => (int) $respons['batas_basah'],
            'jadwal' => $respons['jadwal'],
        ];

        return true;
    }

    private function ringkasanKesalahan(mixed $kesalahan): string
    {
        if (! is_array($kesalahan)) {
            return 'periksa data sensor yang dikirim.';
        }

        $pesan = [];

        foreach ($kesalahan as $field => $daftarPesan) {
            if (is_array($daftarPesan)) {
                $pesan[] = implode(' ', array_filter($daftarPesan, 'is_string'));
            } elseif (is_string($daftarPesan)) {
                $pesan[] = $daftarPesan;
            }
        }

        return $pesan === [] ? 'periksa data sensor yang dikirim.' : implode(' ', $pesan);
    }
}

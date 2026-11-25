<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Perangkat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class JadwalControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_stores_schedule_with_sorted_unique_days(): void
    {
        $perangkat = $this->perangkatTanpaJadwal();

        $response = $this->post(route('jadwal.store'), [
            'jam' => '08:30',
            'durasi' => '15',
            'hari' => ['5', '1', '3'],
        ]);

        $response->assertRedirect(route('jadwal'));
        $this->assertDatabaseHas('jadwal', [
            'perangkat_id' => $perangkat->id,
            'jam' => '08:30:00',
            'durasi_menit' => 15,
        ]);
        $this->assertSame([1, 3, 5], Jadwal::query()->firstOrFail()->hari);
        $this->assertTrue(Jadwal::query()->firstOrFail()->aktif);
    }

    public function test_rejects_invalid_fields_and_preserves_input_for_the_open_form(): void
    {
        $this->perangkatTanpaJadwal();

        $response = $this->from(route('jadwal'))
            ->followingRedirects()
            ->post(route('jadwal.store'), [
                'jam' => '25:61',
                'durasi' => 121,
                'hari' => ['1', '1'],
            ]);

        $response->assertOk()
            ->assertSee('value="25:61"', false)
            ->assertSee('name="durasi" type="number" min="1" max="120" inputmode="numeric" value="121"', false)
            ->assertSeeText('Hari yang sama tidak boleh dipilih lebih dari sekali.');
    }

    public function test_rejects_schedule_overlapping_into_the_following_day(): void
    {
        $perangkat = $this->perangkatTanpaJadwal();
        $perangkat->jadwal()->create([
            'jam' => '23:55:00',
            'durasi_menit' => 10,
            'hari' => [1],
            'aktif' => true,
        ]);

        $response = $this->from(route('jadwal'))->post(route('jadwal.store'), [
            'jam' => '00:01',
            'durasi' => 5,
            'hari' => [2],
        ]);

        $response->assertRedirect(route('jadwal'))
            ->assertSessionHas('error', 'Bentrok dengan jadwal pukul 23:55 pada Sen.')
            ->assertSessionHas('_old_input.jam', '00:01');
        $this->assertSame(1, Jadwal::query()->count());
    }

    public function test_updates_schedule_without_treating_itself_as_a_conflict(): void
    {
        $perangkat = $this->perangkatTanpaJadwal();
        $jadwal = $perangkat->jadwal()->create([
            'jam' => '06:00:00',
            'durasi_menit' => 10,
            'hari' => [1, 2, 3],
            'aktif' => true,
        ]);

        $response = $this->put(route('jadwal.update', $jadwal), [
            'jadwal_id' => $jadwal->id,
            'jam' => '07:15',
            'durasi' => '20',
            'hari' => ['3', '1'],
        ]);

        $response->assertRedirect(route('jadwal'));
        $this->assertDatabaseHas('jadwal', [
            'id' => $jadwal->id,
            'jam' => '07:15:00',
            'durasi_menit' => 20,
        ]);
        $this->assertSame([1, 3], $jadwal->fresh()->hari);
        $this->assertTrue($jadwal->fresh()->aktif);
    }

    public function test_rejects_conflicting_update_and_reopens_the_edit_form_with_old_values(): void
    {
        $perangkat = $this->perangkatTanpaJadwal();
        $perangkat->jadwal()->create([
            'jam' => '23:55:00',
            'durasi_menit' => 10,
            'hari' => [1],
            'aktif' => true,
        ]);
        $jadwal = $perangkat->jadwal()->create([
            'jam' => '12:00:00',
            'durasi_menit' => 5,
            'hari' => [2],
            'aktif' => true,
        ]);

        $response = $this->from(route('jadwal'))
            ->followingRedirects()
            ->put(route('jadwal.update', $jadwal), [
                'jadwal_id' => $jadwal->id,
                'jam' => '00:01',
                'durasi' => '5',
                'hari' => ['2'],
            ]);

        $response->assertOk()
            ->assertSee('action="'.route('jadwal.update', $jadwal).'"', false)
            ->assertSee('value="00:01"', false)
            ->assertSeeText('Bentrok dengan jadwal pukul 23:55 pada Sen.');
        $this->assertDatabaseHas('jadwal', [
            'id' => $jadwal->id,
            'jam' => '12:00:00',
        ]);
    }

    public function test_toggle_refuses_an_overlapping_schedule_and_returns_current_state(): void
    {
        $perangkat = $this->perangkatTanpaJadwal();
        $perangkat->jadwal()->create([
            'jam' => '23:55:00',
            'durasi_menit' => 10,
            'hari' => [1],
            'aktif' => true,
        ]);
        $jadwal = $perangkat->jadwal()->create([
            'jam' => '00:01:00',
            'durasi_menit' => 5,
            'hari' => [2],
            'aktif' => false,
        ]);

        $this->patchJson(route('jadwal.aktif', $jadwal))
            ->assertUnprocessable()
            ->assertExactJson([
                'aktif' => false,
                'pesan' => 'Bentrok dengan jadwal pukul 23:55 pada Sen.',
            ]);

        $this->assertDatabaseHas('jadwal', [
            'id' => $jadwal->id,
            'aktif' => false,
        ]);
    }

    public function test_toggle_activates_and_deactivates_a_schedule_without_conflicts(): void
    {
        $perangkat = $this->perangkatTanpaJadwal();
        $jadwal = $perangkat->jadwal()->create([
            'jam' => '12:00:00',
            'durasi_menit' => 5,
            'hari' => [6],
            'aktif' => false,
        ]);

        $this->patchJson(route('jadwal.aktif', $jadwal))
            ->assertOk()
            ->assertExactJson(['aktif' => true, 'pesan' => 'Jadwal berhasil diaktifkan.']);
        $this->assertDatabaseHas('jadwal', ['id' => $jadwal->id, 'aktif' => true]);

        $this->patchJson(route('jadwal.aktif', $jadwal))
            ->assertOk()
            ->assertExactJson(['aktif' => false, 'pesan' => 'Jadwal berhasil dinonaktifkan.']);
        $this->assertDatabaseHas('jadwal', ['id' => $jadwal->id, 'aktif' => false]);
    }

    private function perangkatTanpaJadwal(): Perangkat
    {
        Jadwal::query()->delete();

        return Perangkat::first() ?? Perangkat::create([
            'nama' => 'Perangkat Pengujian Jadwal',
            'simulasi' => true,
        ]);
    }
}

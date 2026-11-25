@extends('layouts.app')

@section('title', 'Jadwal Penyiraman · SiramSiram')

@section('content')
    @php
        $schedules = array_values(is_array($jadwal ?? null) ? $jadwal : []);
        $weekDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $oldDays = old('hari', range(1, 7));
        $formDays = is_array($oldDays) ? array_map('intval', $oldDays) : [];
        $oldScheduleId = old('jadwal_id');
        $oldSchedule = collect($schedules)->firstWhere('id', (int) $oldScheduleId);
        $formAction = $oldSchedule['url_ubah'] ?? route('jadwal.store');
        $dayError = $errors->first('hari');

        if ($dayError === '') {
            $dayError = collect($errors->getMessages())
                ->first(fn ($messages, $field) => str_starts_with($field, 'hari.'))[0] ?? '';
        }

        $showScheduleDialog = $errors->any() || session()->has('error');
        $activeSchedules = array_filter($schedules, fn ($schedule) => (bool) ($schedule['aktif'] ?? false));
        $nextSchedule = $jadwalBerikutnya;
        $activeCount = count($activeSchedules);
        $inactiveCount = count($schedules) - $activeCount;
        $nextMinutes = null;

        if ($nextSchedule && ! empty($nextSchedule['waktu_mulai'])) {
            $nextMinutes = max(1, (int) ceil(now()->diffInMinutes($nextSchedule['waktu_mulai'])));
        }
    @endphp

    <header class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Jadwal Penyiraman</h1>
            <p class="mt-1 text-base text-muted sm:text-lg">Rutinitas kecil, tanaman lebih sehat.</p>
        </div>
        <span class="flex h-[4.5rem] w-[4.5rem] shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
            <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 9.5a10.5 10.5 0 0 1 14 0M8 12.5a6 6 0 0 1 8 0M11 15.5a1.5 1.5 0 0 1 2 0"/>
                <path d="M12 19h.01"/>
            </svg>
        </span>
    </header>

    <section aria-label="Penyiraman berikutnya" class="rounded-card bg-primary p-6 text-surface shadow-card sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base sm:text-lg">Penyiraman berikutnya</h2>
            <span class="rounded-full bg-primary-dark/70 px-4 py-2 text-sm font-semibold text-surface">
                {{ $nextSchedule ? 'Terjadwal' : 'Tidak ada jadwal' }}
            </span>
        </div>
        <p class="mt-4 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
            @if ($nextSchedule && $nextMinutes !== null)
                @if ($nextMinutes >= 1440)
                    {{ intdiv($nextMinutes, 1440) }} hari
                    @if (intdiv($nextMinutes % 1440, 60) > 0)
                        {{ intdiv($nextMinutes % 1440, 60) }} jam
                    @endif
                @elseif ($nextMinutes >= 60)
                    {{ intdiv($nextMinutes, 60) }} jam {{ $nextMinutes % 60 }} menit
                @else
                    Dalam {{ $nextMinutes }} menit
                @endif
            @else
                Tidak ada jadwal aktif
            @endif
        </p>
        @if ($nextSchedule)
            <p class="mt-4 text-base sm:text-lg">
                {{ $nextSchedule['jam'] ?? '—' }} • durasi {{ $nextSchedule['durasi'] ?? '—' }} menit
            </p>
        @endif
    </section>

    <section aria-labelledby="saved-schedules-title" class="mt-6">
        <div class="mb-6 flex items-center justify-between gap-3">
            <h2 id="saved-schedules-title" class="text-xl font-semibold">Jadwal tersimpan</h2>
            <p class="text-sm text-muted sm:text-base">
                {{ $activeCount }} aktif · {{ $inactiveCount }} nonaktif
            </p>
        </div>

        <div class="space-y-5">
            @foreach ($schedules as $index => $schedule)
                @php
                    $isActive = (bool) ($schedule['aktif'] ?? false);
                    $days = $schedule['hari_label'] ?? [];
                    $daysText = is_array($days) ? implode(' · ', $days) : (string) $days;
                @endphp

                <article class="rounded-card bg-surface p-5 shadow-card sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <svg aria-hidden="true" class="h-7 w-7 shrink-0 {{ $isActive ? 'text-primary' : 'text-muted' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 6v6l4 2"/>
                            </svg>
                            <button type="button" data-edit-schedule="{{ $index }}" class="flex min-w-0 items-center gap-3 text-left focus-visible:rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                <span class="text-3xl font-semibold leading-none sm:text-4xl">{{ $schedule['jam'] ?? '—' }}</span>
                                <span class="text-base text-muted">{{ $schedule['durasi'] ?? '—' }} menit</span>
                            </button>
                        </div>

                        <button
                            type="button"
                            role="switch"
                            aria-checked="{{ $isActive ? 'true' : 'false' }}"
                            aria-label="{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }} jadwal {{ $schedule['jam'] ?? '' }}"
                            data-schedule-toggle
                            data-jam="{{ $schedule['jam'] ?? '' }}"
                            data-url="{{ $schedule['url_aktif'] }}"
                            class="relative h-12 w-[4.875rem] shrink-0 rounded-full {{ $isActive ? 'bg-primary' : 'bg-border' }}"
                        >
                            <span class="absolute top-1 h-10 w-10 rounded-full bg-surface shadow-sm transition-all {{ $isActive ? 'right-1' : 'left-1' }}"></span>
                        </button>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-3">
                        <p class="min-w-0 text-base leading-relaxed text-muted">{{ $daysText }}</p>
                        @if (($schedule['id'] ?? null) === ($nextSchedule['id'] ?? null))
                            <span class="shrink-0 text-sm font-semibold text-primary">Berikutnya</span>
                        @elseif (! $isActive)
                            <span class="shrink-0 text-sm text-muted">Nonaktif</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <p class="mt-6 text-sm text-muted sm:text-base">
        Waktu perangkat • {{ now()->locale('id')->translatedFormat('D, j M Y · H.i') }}
    </p>

    <p id="schedule-toggle-message" aria-live="polite" class="mt-3 hidden text-sm text-accent-red"></p>

    <div class="mt-1 flex justify-end">
        <button
            type="button"
            aria-label="Tambah jadwal"
            aria-haspopup="dialog"
            aria-controls="schedule-dialog"
            class="flex h-[5.25rem] w-[5.25rem] items-center justify-center rounded-[1.5rem] bg-primary-soft text-primary transition hover:bg-primary/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
        >
            <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
        </button>
    </div>

    <dialog id="schedule-dialog" aria-labelledby="schedule-dialog-title" class="fixed inset-0 m-0 h-dvh max-h-none w-full max-w-none overflow-y-auto bg-background p-0 text-primary-dark backdrop:bg-primary-dark/50 lg:m-auto lg:h-auto lg:max-h-[90dvh] lg:max-w-xl lg:rounded-card lg:shadow-card">
        <form id="schedule-form" action="{{ $formAction }}" method="POST" class="flex min-h-dvh flex-col px-6 pb-6 pt-10 sm:px-8 lg:min-h-0 lg:p-8">
            @csrf
            <input id="schedule-method" type="hidden" name="_method" value="PUT" @disabled($oldSchedule === null)>
            <input id="schedule-id" type="hidden" name="jadwal_id" value="{{ old('jadwal_id', $oldSchedule['id'] ?? '') }}">
            <header class="flex items-center gap-5">
                <button type="button" data-close-schedule aria-label="Kembali" class="flex h-9 w-9 shrink-0 items-center justify-center text-primary-dark focus-visible:rounded-lg focus-visible:outline-2 focus-visible:outline-primary">
                    <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </button>
                <h2 id="schedule-dialog-title" class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $oldSchedule ? 'Edit Jadwal' : 'Tambah Jadwal' }}</h2>
            </header>

            <p class="mt-10 text-lg leading-relaxed text-muted">
                Buat rutinitas penyiraman untuk kebun TOGA Anda.
            </p>

            <div class="mt-6 space-y-7 rounded-card bg-surface p-6 shadow-card">
                <label class="block space-y-2">
                    <span class="text-lg font-semibold">Jam mulai</span>
                    <span class="relative block">
                        <input id="schedule-time" name="jam" type="text" inputmode="numeric" placeholder="HH:MM" value="{{ old('jam', '12:00') }}" aria-invalid="@error('jam') true @else false @enderror" @if ($errors->has('jam')) aria-describedby="schedule-time-error" @endif @class(['h-[5.25rem] w-full rounded-2xl border bg-background px-5 pr-16 text-3xl font-medium outline-none focus:ring-2', 'border-accent-red bg-accent-red-soft text-accent-red focus:border-accent-red focus:ring-accent-red/20' => $errors->has('jam'), 'border-border text-primary-dark focus:border-primary focus:ring-primary/20' => ! $errors->has('jam')])>
                        <svg aria-hidden="true" class="pointer-events-none absolute right-5 top-1/2 h-7 w-7 -translate-y-1/2 {{ $errors->has('jam') ? 'text-accent-red' : 'text-primary' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </span>
                    @error('jam')
                        <span id="schedule-time-error" class="block text-base text-accent-red">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block space-y-2">
                    <span class="text-lg font-semibold">Durasi (menit)</span>
                    <span class="relative block">
                        <input id="schedule-duration" name="durasi" type="number" min="1" max="120" inputmode="numeric" value="{{ old('durasi', '2') }}" aria-invalid="@error('durasi') true @else false @enderror" @if ($errors->has('durasi')) aria-describedby="schedule-duration-error" @endif @class(['h-[5.25rem] w-full rounded-2xl border bg-background px-5 pr-16 text-3xl font-medium outline-none focus:ring-2', 'border-accent-red bg-accent-red-soft text-accent-red focus:border-accent-red focus:ring-accent-red/20' => $errors->has('durasi'), 'border-border text-primary-dark focus:border-primary focus:ring-primary/20' => ! $errors->has('durasi')])>
                        <svg aria-hidden="true" class="pointer-events-none absolute right-5 top-1/2 h-7 w-7 -translate-y-1/2 {{ $errors->has('durasi') ? 'text-accent-red' : 'text-primary' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 2h4M12 14l3-3"/>
                            <circle cx="12" cy="14" r="8"/>
                            <path d="M18 5l1 1"/>
                        </svg>
                    </span>
                    @error('durasi')
                        <span id="schedule-duration-error" class="block text-base text-accent-red">{{ $message }}</span>
                    @enderror
                </label>

                <fieldset class="space-y-3" aria-invalid="{{ $dayError !== '' ? 'true' : 'false' }}" @if ($dayError !== '') aria-describedby="schedule-days-error" @endif>
                    <legend class="text-lg font-semibold">Hari</legend>
                    <div class="grid grid-cols-4 gap-3">
                        @foreach ($weekDays as $dayIndex => $day)
                            @php($dayNumber = $dayIndex + 1)
                            @php($daySelected = in_array($dayNumber, $formDays, true))
                            <div>
                                <button type="button" data-schedule-day="{{ $dayNumber }}" aria-pressed="{{ $daySelected ? 'true' : 'false' }}" class="h-[4.5rem] w-full rounded-full px-4 text-base font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary {{ $daySelected ? 'bg-primary-soft text-primary' : 'bg-background text-muted' }}">
                                    {{ $day }}
                                </button>
                                <input type="hidden" name="hari[]" value="{{ $dayNumber }}" data-schedule-day-input="{{ $dayNumber }}" @disabled(! $daySelected)>
                            </div>
                        @endforeach
                    </div>
                    <p id="schedule-repeat-label" class="pt-1 text-base text-muted">Berulang setiap hari</p>
                    @if ($dayError !== '')
                        <span id="schedule-days-error" class="block text-base text-accent-red">{{ $dayError }}</span>
                    @endif
                </fieldset>
            </div>

            @if ($errors->any())
                <aside role="alert" class="mt-6 rounded-card bg-accent-red-soft p-5 text-accent-red">
                    <div class="flex items-start gap-4">
                        <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 11v5M12 8h.01"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold">Periksa kembali isian Anda</h3>
                            <p class="mt-1 text-base leading-relaxed">Gunakan format 24 jam dan durasi 1 sampai 120 menit sebelum menyimpan.</p>
                        </div>
                    </div>
                </aside>
            @endif

            @if (session('error'))
                <aside role="alert" class="mt-6 rounded-card bg-accent-red-soft p-5 text-accent-red">
                    <div class="flex items-start gap-4">
                        <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 11v5M12 8h.01"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold">Jadwal bentrok dengan jadwal lain</h3>
                            <p class="mt-1 text-base leading-relaxed">{{ session('error') }}</p>
                        </div>
                    </div>
                </aside>
            @endif

            @unless ($errors->any() || session()->has('error'))
                <aside aria-live="polite" class="mt-6 rounded-card bg-primary-soft p-5 text-primary">
                <div class="flex items-start gap-4">
                    <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 11v5M12 8h.01"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold">Siap dijadwalkan</h3>
                        <p id="schedule-summary" class="mt-1 text-base leading-relaxed"></p>
                    </div>
                </div>
                </aside>
            @endunless

            <div class="mt-auto grid grid-cols-2 gap-4 pt-8 lg:mt-8">
                <button type="button" data-close-schedule class="h-[4.5rem] rounded-full bg-primary-soft px-4 text-lg font-semibold text-primary transition hover:bg-primary/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    Batal
                </button>
                <button type="submit" data-save-schedule class="flex h-[4.5rem] items-center justify-center gap-3 rounded-full bg-primary px-4 text-lg font-semibold text-surface transition hover:bg-primary/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 5 5L20 7"/>
                    </svg>
                    Simpan
                </button>
            </div>
        </form>
    </dialog>

    <script>
        const scheduleDialog = document.getElementById('schedule-dialog');
        const openScheduleDialog = document.querySelector('[aria-controls="schedule-dialog"]');
        const scheduleForm = document.getElementById('schedule-form');
        const scheduleTime = document.getElementById('schedule-time');
        const scheduleDuration = document.getElementById('schedule-duration');
        const scheduleSummary = document.getElementById('schedule-summary');
        const repeatLabel = document.getElementById('schedule-repeat-label');
        const scheduleTitle = document.getElementById('schedule-dialog-title');
        const dayButtons = Array.from(scheduleDialog.querySelectorAll('[data-schedule-day]'));
        const weekDays = @js($weekDays);
        const dayNumbers = @js(range(1, 7));
        const schedules = @js($schedules);
        let selectedDays = @js($formDays);

        const updateScheduleSummary = () => {
            const parsedDuration = Number.parseInt(scheduleDuration.value || '0', 10);
            const duration = Number.isFinite(parsedDuration) ? Math.max(0, parsedDuration) : 0;
            const timeMatch = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(scheduleTime.value || '');
            const [hours, minutes] = timeMatch ? timeMatch.slice(1).map(Number) : [12, 0];
            const endTime = new Date();
            endTime.setHours(hours, minutes + duration);
            const endHour = String(endTime.getHours()).padStart(2, '0');
            const endMinute = String(endTime.getMinutes()).padStart(2, '0');
            const selectedDayLabels = selectedDays.map((day) => weekDays[day - 1]);

            if (scheduleSummary) {
                scheduleSummary.textContent = `Setiap ${selectedDayLabels.join(', ')} pukul ${scheduleTime.value || '12:00'}–${endHour}.${endMinute}. Bentrok diperiksa sebelum jadwal disimpan.`;
            }
            repeatLabel.textContent = selectedDays.length === dayNumbers.length
                ? 'Berulang setiap hari'
                : selectedDays.length
                    ? `Berulang setiap ${selectedDayLabels.join(' · ')}`
                    : 'Pilih minimal satu hari';
        };

        const setSelectedDays = (days) => {
            const selectedDayNumbers = days.map(Number);
            selectedDays = dayNumbers.filter((day) => selectedDayNumbers.includes(day));
            dayButtons.forEach((button) => {
                const isSelected = selectedDays.includes(Number(button.dataset.scheduleDay));
                button.setAttribute('aria-pressed', String(isSelected));
                button.classList.toggle('bg-primary-soft', isSelected);
                button.classList.toggle('text-primary', isSelected);
                button.classList.toggle('bg-background', ! isSelected);
                button.classList.toggle('text-muted', ! isSelected);
                scheduleDialog.querySelector(`[data-schedule-day-input="${button.dataset.scheduleDay}"]`).disabled = ! isSelected;
            });
            updateScheduleSummary();
        };

        const openScheduleForm = (schedule = null) => {
            scheduleForm.dataset.editing = schedule ? 'true' : 'false';
            scheduleTitle.textContent = schedule ? 'Edit Jadwal' : 'Tambah Jadwal';
            scheduleForm.action = schedule?.url_ubah ?? @js(route('jadwal.store'));
            scheduleForm.querySelector('#schedule-method').disabled = ! schedule;
            scheduleForm.querySelector('#schedule-id').value = schedule?.id ?? '';
            scheduleTime.value = String(schedule?.jam ?? '12:00').replace('.', ':');
            scheduleDuration.value = schedule?.durasi ?? '2';
            setSelectedDays(schedule?.hari ?? dayNumbers);
            scheduleDialog.showModal();
            scheduleDialog.querySelector('[data-close-schedule]').focus();
        };

        openScheduleDialog.addEventListener('click', () => openScheduleForm());
        document.querySelectorAll('[data-edit-schedule]').forEach((button) => {
            button.addEventListener('click', () => openScheduleForm(schedules[Number(button.dataset.editSchedule)]));
        });
        scheduleDialog.querySelectorAll('[data-close-schedule]').forEach((button) => {
            button.addEventListener('click', () => scheduleDialog.close());
        });
        dayButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const day = Number(button.dataset.scheduleDay);
                setSelectedDays(selectedDays.includes(day)
                    ? selectedDays.filter((selectedDay) => selectedDay !== day)
                    : [...selectedDays, day]);
            });
        });
        scheduleTime.addEventListener('input', updateScheduleSummary);
        scheduleDuration.addEventListener('input', updateScheduleSummary);
        scheduleDialog.addEventListener('click', (event) => {
            if (event.target === scheduleDialog) {
                scheduleDialog.close();
            }
        });
        const toggleMessage = document.getElementById('schedule-toggle-message');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const updateToggle = (button, aktif) => {
            button.setAttribute('aria-checked', String(aktif));
            button.setAttribute(
                'aria-label',
                `${aktif ? 'Nonaktifkan' : 'Aktifkan'} jadwal ${button.dataset.jam}`,
            );
            button.classList.toggle('bg-primary', aktif);
            button.classList.toggle('bg-border', ! aktif);
            button.querySelector('span').classList.toggle('right-1', aktif);
            button.querySelector('span').classList.toggle('left-1', ! aktif);
        };

        document.querySelectorAll('[data-schedule-toggle]').forEach((button) => {
            button.addEventListener('click', async () => {
                const aktifSebelumnya = button.getAttribute('aria-checked') === 'true';
                toggleMessage.classList.add('hidden');
                toggleMessage.textContent = '';
                updateToggle(button, ! aktifSebelumnya);
                button.disabled = true;

                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'PATCH',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    const hasil = await response.json().catch(() => ({}));

                    if (! response.ok || typeof hasil.aktif !== 'boolean') {
                        throw new Error(hasil.pesan ?? 'Jadwal gagal diperbarui. Silakan coba lagi.');
                    }

                    window.location.reload();
                } catch (error) {
                    updateToggle(button, aktifSebelumnya);
                    button.disabled = false;
                    toggleMessage.textContent = error instanceof Error
                        ? error.message
                        : 'Jadwal gagal diperbarui. Silakan coba lagi.';
                    toggleMessage.classList.remove('hidden');
                }
            });
        });

        updateScheduleSummary();
        if (@json($showScheduleDialog)) {
            scheduleDialog.showModal();
        }
    </script>
@endsection

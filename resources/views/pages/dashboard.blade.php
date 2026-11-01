@extends('layouts.app')

@section('title', 'Beranda · SiramSiram')

@section('content')
    @php
        $soilMoisture = (int) ($sensor['soil_moisture'] ?? 0);
    @endphp

    <header class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">SiramSiram</h1>
                <p class="mt-1 text-base text-muted sm:text-lg">Tanaman terawat, keluarga sehat.</p>
            </div>
            <span class="flex h-[4.5rem] w-[4.5rem] shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21v-9"/>
                    <path d="M12 13c-5.5 0-8-2.7-8-7 5.2 0 8 2.4 8 7Z"/>
                    <path d="M12 10c0-4.1 2.5-6.5 8-6.5 0 4.7-2.7 7-8 7"/>
                    <path d="M7 21h10"/>
                </svg>
            </span>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Kebun TOGA saya</h2>
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-soft px-4 py-2 text-sm font-semibold text-primary">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 9.5a10.5 10.5 0 0 1 14 0M8 12.5a6 6 0 0 1 8 0M11 15.5a1.5 1.5 0 0 1 2 0"/>
                    <path d="M12 19h.01"/>
                </svg>
                {{ $status['connection'] ?? '—' }}
            </span>
        </div>
    </header>

    <section aria-label="Ringkasan sensor dan penyiraman" class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-card bg-surface p-6 shadow-card lg:col-span-2 xl:col-span-3">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Kelembapan Tanah</h2>
                <svg aria-hidden="true" class="h-7 w-7 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21v-9"/>
                    <path d="M12 13c-5.5 0-8-2.7-8-7 5.2 0 8 2.4 8 7Z"/>
                    <path d="M12 10c0-4.1 2.5-6.5 8-6.5 0 4.7-2.7 7-8 7"/>
                    <path d="M7 21h10"/>
                </svg>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
                <p class="text-6xl font-semibold leading-none tracking-tight sm:text-7xl">
                    {{ $sensor['soil_moisture'] ?? '—' }}<span class="text-4xl sm:text-5xl">%</span>
                </p>
                <span class="inline-flex shrink-0 items-center gap-2 rounded-full bg-primary-soft px-4 py-2 text-sm font-semibold text-primary">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                    {{ $status['soil_moisture'] ?? '—' }}
                </span>
            </div>

            <div class="mt-6 h-4 overflow-hidden rounded-full bg-primary-soft" role="progressbar" aria-label="Kelembapan tanah" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $soilMoisture }}">
                <div class="h-full rounded-full bg-primary" style="width: {{ $soilMoisture }}%"></div>
            </div>
            <p class="mt-3 text-base text-muted">Rata-rata 5 pembacaan terakhir</p>

            <div class="mt-4 flex justify-between gap-2 text-xs font-medium sm:text-sm">
                <span class="text-accent-amber">Kering &lt;30%</span>
                <span class="text-primary">Normal 30–&lt;70%</span>
                <span class="text-accent-blue">Basah &gt;70%</span>
            </div>
        </article>

        <article class="rounded-card bg-surface p-6 shadow-card">
            <div class="flex items-center gap-3 text-muted">
                <svg aria-hidden="true" class="h-6 w-6 text-accent-amber" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 14.8V5a2 2 0 0 0-4 0v9.8a5 5 0 1 0 4 0Z"/>
                    <path d="M12 11v7"/>
                </svg>
                <h2 class="text-base">Suhu</h2>
            </div>
            <p class="mt-4 text-4xl font-semibold leading-none">{{ $sensor['temperature'] ?? '—' }}°C</p>
        </article>

        <article class="rounded-card bg-surface p-6 shadow-card">
            <div class="flex items-center gap-3 text-muted">
                <svg aria-hidden="true" class="h-6 w-6 text-accent-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5c-2.2 3.3-5 6.1-5 10.1a5 5 0 0 0 10 0c0-4-2.8-6.8-5-10.1Z"/>
                    <path d="M19.5 5.5c-1 1.4-2.2 2.7-2.7 4.3a4.5 4.5 0 1 1-1 8.5"/>
                </svg>
                <h2 class="text-base">Kelembapan Udara</h2>
            </div>
            <p class="mt-4 text-4xl font-semibold leading-none">{{ $sensor['air_humidity'] ?? '—' }}%</p>
        </article>

        <article class="rounded-card bg-surface p-6 shadow-card lg:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-xl font-semibold">Status Penyiraman</h2>
                <span class="rounded-full bg-primary-soft px-4 py-2 text-sm font-semibold text-primary">
                    {{ $status['watering_schedule'] ?? '—' }}
                </span>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <svg aria-hidden="true" class="h-7 w-7 shrink-0 text-accent-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5c-2.2 3.3-5 6.1-5 10.1a5 5 0 0 0 10 0c0-4-2.8-6.8-5-10.1Z"/>
                    <path d="M19.5 5.5c-1 1.4-2.2 2.7-2.7 4.3a4.5 4.5 0 1 1-1 8.5"/>
                </svg>
                <p class="text-xl font-semibold">{{ $status['watering'] ?? '—' }}</p>
            </div>

            <p class="mt-4 text-base leading-6 text-muted">
                Penyiraman berikutnya dalam {{ $status['next_watering_in'] ?? '—' }}
            </p>
            <div class="mt-4 flex items-center justify-between gap-3 text-base">
                <span class="text-muted">Jadwal berikutnya</span>
                <span class="text-right font-semibold">
                    {{ $status['next_schedule_time'] ?? '—' }} • {{ $status['next_schedule_duration'] ?? '—' }}
                </span>
            </div>
        </article>
    </section>

    <p class="mt-6 text-center text-sm text-muted sm:text-base">
        Terakhir diperbarui • {{ now()->locale('id')->translatedFormat('D, j M Y · H.i') }}
    </p>
@endsection

@extends('layouts.app')

@section('title', 'Statistik Monitoring · SiramSiram')

@section('content')
    <header class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Statistik Monitoring</h1>
            <p class="mt-1 text-base text-muted sm:text-lg">Kenali kebutuhan tanaman Anda.</p>
        </div>
        <span class="flex h-[4.5rem] w-[4.5rem] shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
            <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 9.5a10.5 10.5 0 0 1 14 0M8 12.5a6 6 0 0 1 8 0M11 15.5a1.5 1.5 0 0 1 2 0"/>
                <path d="M12 19h.01"/>
            </svg>
        </span>
    </header>

    <div role="group" aria-label="Filter periode statistik" class="mb-6 flex rounded-full bg-primary-soft p-1.5">
        <button type="button" data-statistics-period="hari_ini" aria-pressed="true" class="flex-1 rounded-full bg-primary px-4 py-3 text-base font-semibold text-surface transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            Hari ini
        </button>
        <button type="button" data-statistics-period="tujuh_hari" aria-pressed="false" class="flex-1 rounded-full px-4 py-3 text-base font-semibold text-primary transition hover:bg-surface/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            7 Hari
        </button>
    </div>

    <section aria-label="Ringkasan kelembapan" class="rounded-card bg-surface p-6 shadow-card">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-base text-muted">Rata-rata Kelembapan</p>
                <p data-average-moisture class="mt-2 text-5xl font-semibold leading-none">—</p>
            </div>
            <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-primary-soft text-primary">
                <svg aria-hidden="true" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21v-9"/>
                    <path d="M12 13c-5.5 0-8-2.7-8-7 5.2 0 8 2.4 8 7Z"/>
                    <path d="M12 10c0-4.1 2.5-6.5 8-6.5 0 4.7-2.7 7-8 7"/>
                    <path d="M7 21h10"/>
                </svg>
            </span>
        </div>
        <p class="mt-4 text-base font-medium text-primary">Kelembapan terjaga dalam rentang normal</p>
    </section>

    <section aria-label="Suhu" class="mt-6 grid grid-cols-2 gap-4 sm:gap-6">
        <article class="rounded-card bg-surface p-5 shadow-card sm:p-6">
            <h2 class="text-base text-muted">Suhu Minimum</h2>
            <p data-minimum-temperature class="mt-3 text-4xl font-semibold leading-none">—</p>
        </article>
        <article class="rounded-card bg-surface p-5 shadow-card sm:p-6">
            <h2 class="text-base text-muted">Suhu Maksimum</h2>
            <p data-maximum-temperature class="mt-3 text-4xl font-semibold leading-none">—</p>
        </article>
    </section>

    <section aria-labelledby="soil-moisture-title" class="mt-6 min-w-0 overflow-hidden rounded-card bg-surface p-6 shadow-card">
        <div class="flex items-center justify-between gap-3">
            <h2 id="soil-moisture-title" class="text-xl font-semibold">Kelembapan Tanah</h2>
            <span data-moisture-range class="shrink-0 rounded-full bg-primary-soft px-4 py-2 text-sm font-semibold text-primary">—</span>
        </div>
        <p data-date-range class="mt-4 text-sm text-muted sm:text-base"></p>

        <figure class="mt-5 min-w-0">
            <div class="relative h-56 w-full max-w-full sm:h-72">
                <canvas
                    data-soil-moisture-chart
                    data-periods='@json($statistik["periods"])'
                    role="img"
                    aria-label="Grafik garis riwayat kelembapan tanah. Gunakan ringkasan teks di bawah untuk nilai minimum, maksimum, dan rata-rata."
                ></canvas>
            </div>
            <figcaption class="mt-5 space-y-3">
                <div class="flex items-center gap-2 text-sm text-muted sm:text-base">
                    <span aria-hidden="true" class="h-1 w-6 bg-primary"></span>
                    <span>Kelembapan tanah (%) • <span data-reading-count></span> pembacaan</span>
                </div>
                <p data-chart-summary class="text-sm leading-relaxed text-muted sm:text-base"></p>
            </figcaption>
        </figure>
    </section>
@endsection

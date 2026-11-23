<div class="space-y-6">
    <p class="text-lg leading-relaxed text-muted">
        Mode Otomatis • Penyiraman mengikuti kelembapan tanah, bukan jadwal.
    </p>

    <section aria-labelledby="automatic-pump-title" class="rounded-card bg-surface p-6 shadow-card">
        <div class="flex items-center justify-between gap-3">
            <h2 id="automatic-pump-title" class="text-xl font-semibold">Kontrol pompa</h2>
            <span class="shrink-0 rounded-full bg-background px-4 py-2 text-sm font-semibold text-muted">
                {{ $pompaManual ? 'Menyiram' : 'Tidak Menyiram' }}
            </span>
        </div>

        <div class="flex flex-col items-center py-6">
            <button type="button" disabled class="flex h-44 w-44 cursor-not-allowed flex-col items-center justify-center gap-3 rounded-full bg-border text-muted">
                <svg aria-hidden="true" class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5c-2.2 3.3-5 6.1-5 10.1a5 5 0 0 0 10 0c0-4-2.8-6.8-5-10.1Z"/>
                </svg>
                <span class="text-lg font-semibold">Mulai Siram</span>
            </button>
            <p class="mt-4 text-center text-base text-muted">Kontrol manual dinonaktifkan</p>
        </div>

        <div class="flex items-center justify-between gap-3 text-base">
            <span class="text-muted">Kelembapan tanah</span>
            <span class="text-right font-semibold">{{ $kelembapanTanah ?? '—' }}% · {{ $statusKelembapanTanah ?? '—' }}</span>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <aside aria-label="Informasi mode otomatis" class="rounded-card bg-accent-blue/10 p-5 text-accent-blue">
            <div class="flex items-start gap-4">
                <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 11v5M12 8h.01"/>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold">Tanaman disiram saat membutuhkan</h2>
                    <p class="mt-1 text-base leading-relaxed">
                        Pompa menyala saat tanah kering (&lt;{{ $batasKering }}%) dan berhenti saat kelembapan mencapai {{ $batasBasah }}%.
                    </p>
                </div>
            </div>
        </aside>

        <section aria-labelledby="automatic-moisture-title" class="rounded-card bg-surface p-6 shadow-card">
            <h2 id="automatic-moisture-title" class="text-xl font-semibold">
                {{ $statusKelembapanTanah === 'Kering' ? 'Tanah membutuhkan penyiraman' : 'Tanah masih cukup lembap' }}
            </h2>
            <p class="mt-3 text-base leading-relaxed text-muted">
                @if ($kelembapanTanah === null)
                    Belum ada pembacaan sensor.
                @else
                    Pembacaan saat ini {{ $kelembapanTanah }}%.
                @endif
            </p>
            <div class="mt-3 flex items-center justify-between gap-3 text-base">
                <span class="text-muted">Kontrol manual</span>
                <span class="text-right font-semibold">Pilih mode Manual</span>
            </div>
        </section>
    </div>
</div>

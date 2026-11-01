<div class="space-y-6" data-manual-control>
    <p class="text-lg leading-relaxed text-muted">
        Mode Manual • Anda menentukan kapan penyiraman dimulai dan dihentikan.
    </p>

    <div data-manual-state="idle">
        <section aria-labelledby="manual-pump-title" class="rounded-card bg-surface p-6 shadow-card">
            <div class="flex items-center justify-between gap-3">
                <h2 id="manual-pump-title" class="text-xl font-semibold">Kontrol pompa</h2>
                <span class="shrink-0 rounded-full bg-background px-4 py-2 text-sm font-semibold text-muted">
                    Tidak Menyiram
                </span>
            </div>

            <div class="flex flex-col items-center py-6">
                <button type="button" data-start-watering class="flex h-44 w-44 flex-col items-center justify-center gap-3 rounded-full bg-accent-blue text-surface shadow-sm transition hover:bg-accent-blue/90 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-blue">
                    <svg aria-hidden="true" class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3.5c-2.2 3.3-5 6.1-5 10.1a5 5 0 0 0 10 0c0-4-2.8-6.8-5-10.1Z"/>
                    </svg>
                    <span class="text-lg font-semibold">Mulai Siram</span>
                </button>
                <p class="mt-4 text-center text-base text-muted">Ketuk untuk mulai menyiram</p>
            </div>

            <div class="flex items-center justify-between gap-3 text-base">
                <span class="text-muted">Kelembapan tanah</span>
                <span class="text-right font-semibold">50% · Normal</span>
            </div>
        </section>

        <div class="mt-6 space-y-6">
            <aside aria-label="Informasi pompa" class="rounded-card bg-accent-blue/10 p-5 text-accent-blue">
                <div class="flex items-start gap-4">
                    <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 11v5M12 8h.01"/>
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold">Pompa belum dinyalakan</h2>
                        <p class="mt-1 text-base leading-relaxed">
                            Perintah mulai belum diterima ESP32. Pompa tetap mati selama mencoba kembali.
                        </p>
                    </div>
                </div>
            </aside>

            <div role="status" class="rounded-card bg-primary-dark px-5 py-5 text-base leading-relaxed text-surface lg:mt-12">
                Perintah gagal terkirim, mencoba lagi dalam 4 detik (percobaan 2)
            </div>
        </div>
    </div>

    <div data-manual-state="watering" class="hidden">
        <section aria-labelledby="manual-active-pump-title" class="rounded-card bg-surface p-6 shadow-card">
            <div class="flex items-center justify-between gap-3">
                <h2 id="manual-active-pump-title" class="text-xl font-semibold">Kontrol pompa</h2>
                <span class="shrink-0 rounded-full bg-accent-blue/10 px-4 py-2 text-sm font-semibold text-accent-blue">
                    Menyiram
                </span>
            </div>

            <div class="flex flex-col items-center py-6">
                <button type="button" data-stop-watering class="flex h-44 w-44 flex-col items-center justify-center gap-3 rounded-full bg-accent-red-soft text-accent-red transition hover:bg-accent-red-soft/80 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-red">
                    <svg aria-hidden="true" class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                    <span class="text-lg font-semibold">Hentikan Siram</span>
                </button>
                <p class="mt-4 text-center text-base text-muted">Berjalan 00:32 • ketuk untuk berhenti</p>
            </div>

            <div class="flex items-center justify-between gap-3 text-base">
                <span class="text-muted">Sumber perintah</span>
                <span class="text-right font-semibold">Manual</span>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <aside aria-label="Informasi pompa" class="rounded-card bg-primary-soft p-5 text-primary">
                <div class="flex items-start gap-4">
                    <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 11v5M12 8h.01"/>
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold">Perintah diterima</h2>
                        <p class="mt-1 text-base leading-relaxed">
                            ESP32 sedang menyalakan pompa. Hentikan penyiraman saat air sudah cukup.
                        </p>
                    </div>
                </div>
            </aside>

            <section aria-labelledby="manual-details-title" class="rounded-card bg-surface p-6 shadow-card">
                <h2 id="manual-details-title" class="text-xl font-semibold">Rincian penyiraman</h2>
                <dl class="mt-3 space-y-3 text-base">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Mulai menyiram</dt>
                        <dd class="text-right font-semibold">07.39.28</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Koneksi ESP32</dt>
                        <dd class="text-right font-semibold">Terhubung</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-manual-control]').forEach((control) => {
        const idleState = control.querySelector('[data-manual-state="idle"]');
        const wateringState = control.querySelector('[data-manual-state="watering"]');

        control.querySelector('[data-start-watering]').addEventListener('click', () => {
            idleState.classList.add('hidden');
            wateringState.classList.remove('hidden');
        });

        control.querySelector('[data-stop-watering]').addEventListener('click', () => {
            wateringState.classList.add('hidden');
            idleState.classList.remove('hidden');
        });
    });
</script>

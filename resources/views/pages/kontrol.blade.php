@extends('layouts.app')

@section('title', 'Kontrol Penyiraman · SiramSiram')

@section('content')
    @php
        $activeMode = strtolower((string) ($modeAktif ?? 'manual'));

        if (! in_array($activeMode, ['manual', 'otomatis', 'terjadwal'], true)) {
            $activeMode = 'manual';
        }
    @endphp

    <header class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Kontrol Penyiraman</h1>
            <p class="mt-1 text-base text-muted sm:text-lg">Atur air sesuai kebutuhan tanaman.</p>
        </div>
        <span class="flex h-[4.5rem] w-[4.5rem] shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
            <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 9.5a10.5 10.5 0 0 1 14 0M8 12.5a6 6 0 0 1 8 0M11 15.5a1.5 1.5 0 0 1 2 0"/>
                <path d="M12 19h.01"/>
            </svg>
        </span>
    </header>

    <div data-mode-tabs>
        <div role="tablist" aria-label="Mode penyiraman" aria-orientation="horizontal" class="flex rounded-full bg-primary-soft p-1.5">
            <button
                id="mode-tab-otomatis"
                type="button"
                role="tab"
                aria-controls="mode-panel-otomatis"
                aria-selected="{{ $activeMode === 'otomatis' ? 'true' : 'false' }}"
                tabindex="{{ $activeMode === 'otomatis' ? '0' : '-1' }}"
                data-mode-tab="otomatis"
                class="flex-1 rounded-full px-2 py-3 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:text-base {{ $activeMode === 'otomatis' ? 'bg-primary text-surface' : 'text-primary-dark hover:bg-surface/60' }}"
            >Otomatis</button>
            <button
                id="mode-tab-manual"
                type="button"
                role="tab"
                aria-controls="mode-panel-manual"
                aria-selected="{{ $activeMode === 'manual' ? 'true' : 'false' }}"
                tabindex="{{ $activeMode === 'manual' ? '0' : '-1' }}"
                data-mode-tab="manual"
                class="flex-1 rounded-full px-2 py-3 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:text-base {{ $activeMode === 'manual' ? 'bg-primary text-surface' : 'text-primary-dark hover:bg-surface/60' }}"
            >Manual</button>
            <button
                id="mode-tab-terjadwal"
                type="button"
                role="tab"
                aria-controls="mode-panel-terjadwal"
                aria-selected="{{ $activeMode === 'terjadwal' ? 'true' : 'false' }}"
                tabindex="{{ $activeMode === 'terjadwal' ? '0' : '-1' }}"
                data-mode-tab="terjadwal"
                class="flex-1 rounded-full px-2 py-3 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:text-base {{ $activeMode === 'terjadwal' ? 'bg-primary text-surface' : 'text-primary-dark hover:bg-surface/60' }}"
            >Terjadwal</button>
        </div>

        <div class="mt-6">
            <section
                id="mode-panel-manual"
                role="tabpanel"
                aria-labelledby="mode-tab-manual"
                tabindex="0"
                data-mode-panel="manual"
                @class(['hidden' => $activeMode !== 'manual'])
            >
                @include('partials.kontrol.manual')
            </section>
            <section
                id="mode-panel-otomatis"
                role="tabpanel"
                aria-labelledby="mode-tab-otomatis"
                tabindex="0"
                data-mode-panel="otomatis"
                @class(['hidden' => $activeMode !== 'otomatis'])
            >
                @include('partials.kontrol.otomatis')
            </section>
            <section
                id="mode-panel-terjadwal"
                role="tabpanel"
                aria-labelledby="mode-tab-terjadwal"
                tabindex="0"
                data-mode-panel="terjadwal"
                @class(['hidden' => $activeMode !== 'terjadwal'])
            >
                @include('partials.kontrol.terjadwal')
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-mode-tabs]').forEach((tabSet) => {
            const tabs = Array.from(tabSet.querySelectorAll('[data-mode-tab]'));
            const panels = Array.from(tabSet.querySelectorAll('[data-mode-panel]'));

            const activateTab = (tab, moveFocus = false) => {
                tabs.forEach((item) => {
                    const isSelected = item === tab;
                    item.setAttribute('aria-selected', String(isSelected));
                    item.tabIndex = isSelected ? 0 : -1;
                    item.classList.toggle('bg-primary', isSelected);
                    item.classList.toggle('text-surface', isSelected);
                    item.classList.toggle('text-primary-dark', ! isSelected);
                    item.classList.toggle('hover:bg-surface/60', ! isSelected);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.modePanel !== tab.dataset.modeTab);
                });

                if (moveFocus) {
                    tab.focus();
                }
            };

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => activateTab(tab));
                tab.addEventListener('keydown', (event) => {
                    let nextIndex;

                    if (event.key === 'ArrowRight') {
                        nextIndex = (index + 1) % tabs.length;
                    } else if (event.key === 'ArrowLeft') {
                        nextIndex = (index - 1 + tabs.length) % tabs.length;
                    } else if (event.key === 'Home') {
                        nextIndex = 0;
                    } else if (event.key === 'End') {
                        nextIndex = tabs.length - 1;
                    } else {
                        return;
                    }

                    event.preventDefault();
                    activateTab(tabs[nextIndex], true);
                });
            });
        });
    </script>
@endsection

@extends('layouts.app')

@section('title', 'Status Koneksi · SiramSiram')
@section('hide-bottom-nav', true)

@section('content')
    @php
        $connectionState = $koneksi['status'];
        $isConnecting = $connectionState === 'menghubungkan';
        $connectionStates = [
            'terhubung' => [
                'title' => 'Terhubung',
                'description' => 'ESP32 siap merawat tanaman Anda.',
                'info' => 'Ponsel dan ESP32 berada di jaringan Wi-Fi yang sama. Data sensor diperbarui otomatis.',
                'action' => 'Periksa Koneksi',
            ],
            'menghubungkan' => [
                'title' => 'Menghubungkan Ulang',
                'description' => 'Mencoba tersambung kembali ke ESP32...',
                'info' => 'Jangan tutup aplikasi. Perintah penyiraman ditunda hingga koneksi tersedia kembali.',
                'action' => 'Menghubungkan...',
            ],
            'terputus' => [
                'title' => 'Terputus',
                'description' => 'Koneksi ke perangkat ESP32 terhenti.',
                'info' => 'Tidak terhubung ke SiramSiram. Perintah tidak dapat dikirim.',
                'action' => 'Coba Hubungkan Lagi',
            ],
        ];
    @endphp

    <div class="mx-auto flex min-h-[calc(100dvh-5rem)] w-full max-w-xl flex-col">
        <header class="mb-8 flex items-center gap-5">
            <a href="{{ route('dashboard') }}" aria-label="Kembali ke Beranda" class="flex h-9 w-9 shrink-0 items-center justify-center text-primary-dark focus-visible:rounded-lg focus-visible:outline-2 focus-visible:outline-primary">
                <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Status Koneksi</h1>
        </header>

        <section data-connection-status aria-live="polite" aria-atomic="true" class="rounded-card p-8 text-center {{ $connectionState === 'terhubung' ? 'bg-primary-soft text-primary' : ($isConnecting ? 'bg-accent-amber-soft text-accent-amber' : 'bg-accent-red-soft text-accent-red') }}">
            <span class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-surface">
                <svg data-status-icon="terhubung" aria-hidden="true" @class(['h-12 w-12' => true, 'hidden' => $connectionState !== 'terhubung']) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 8a14 14 0 0 1 18 0M6.5 11.5a9 9 0 0 1 11 0M10 15a4 4 0 0 1 4 0"/>
                    <path d="M12 19h.01"/>
                </svg>
                <svg data-status-icon="menghubungkan" aria-hidden="true" @class(['h-12 w-12 animate-spin' => true, 'hidden' => ! $isConnecting]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M20 12a8 8 0 1 1-2.34-5.66"/>
                </svg>
                <svg data-status-icon="terputus" aria-hidden="true" @class(['h-12 w-12' => true, 'hidden' => $connectionState !== 'terputus']) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 8a14 14 0 0 1 18 0M6.5 11.5a9 9 0 0 1 11 0M10 15a4 4 0 0 1 4 0"/>
                    <path d="m4 4 16 16"/>
                </svg>
            </span>
            <h2 data-status-title class="mt-5 text-3xl font-semibold sm:text-4xl">{{ $koneksi['judul'] }}</h2>
            <p data-status-description class="mt-4 text-base leading-relaxed sm:text-lg">{{ $koneksi['keterangan'] }}</p>
        </section>

        <section aria-labelledby="ip-address-title" class="mt-6 rounded-card bg-surface p-6 shadow-card">
            <h2 id="ip-address-title" class="text-lg font-semibold">Alamat IP ESP32</h2>
            <div data-ip-field class="relative mt-3 flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-4">
                <svg aria-hidden="true" class="h-7 w-7 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="5" width="14" height="14" rx="2"/>
                    <path d="M9 9h6v6H9zM9 1v4M15 1v4M9 19v4M15 19v4M1 9h4M1 15h4M19 9h4M19 15h4"/>
                </svg>
                <input id="device-ip" aria-label="Alamat IP ESP32" value="{{ $koneksi['ip'] }}" readonly class="min-w-0 flex-1 bg-transparent text-xl text-primary-dark outline-none sm:text-2xl">
                <button type="button" data-edit-ip aria-label="Ubah alamat IP" aria-pressed="false" class="relative z-10 flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-muted focus-visible:outline-2 focus-visible:outline-primary">
                    <svg data-edit-icon aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m16 5 3 3M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16z"/>
                    </svg>
                    <svg data-confirm-icon aria-hidden="true" class="hidden h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m5 12 5 5L20 7"/>
                    </svg>
                </button>
            </div>
            <p id="device-ip-error" aria-live="polite" class="mt-2 hidden text-sm text-accent-red">Format alamat IP tidak valid</p>
        </section>

        <section aria-labelledby="device-network-title" class="mt-6 rounded-card bg-surface p-6 shadow-card">
            <h2 id="device-network-title" class="text-xl font-semibold">Jaringan perangkat</h2>
            <dl class="mt-5 space-y-4 text-base">
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-muted">Nama Wi-Fi</dt>
                    <dd class="text-right font-semibold">{{ $koneksi['wifi'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-muted">Perangkat</dt>
                    <dd class="text-right font-semibold">{{ $koneksi['perangkat'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-muted">{{ $koneksi['label_waktu'] }}</dt>
                    <dd class="text-right font-semibold">{{ $koneksi['terakhir'] }}</dd>
                </div>
                @if ($isConnecting)
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-muted">Percobaan</dt>
                        <dd class="text-right font-semibold">2 dari 5</dd>
                    </div>
                @endif
            </dl>
        </section>

        <aside data-connection-info aria-live="polite" class="mt-6 flex items-start gap-4 rounded-card p-5 {{ $connectionState === 'terhubung' ? 'bg-primary-soft text-primary' : ($isConnecting ? 'bg-accent-amber-soft text-accent-amber' : 'bg-accent-red-soft text-accent-red') }}">
            <svg aria-hidden="true" class="mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>
            <p data-info-message class="text-base leading-relaxed">{{ $koneksi['info'] }}</p>
        </aside>

        <button type="button" data-check-connection @disabled($isConnecting) class="mt-auto flex min-h-[4.5rem] w-full items-center justify-center gap-3 rounded-full px-5 py-4 text-lg font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary {{ $connectionState === 'terputus' ? 'bg-primary text-surface hover:bg-primary/90' : ($isConnecting ? 'cursor-not-allowed bg-border text-muted' : 'bg-primary-soft text-primary hover:bg-primary/15') }}">
            <svg data-retry-icon aria-hidden="true" class="h-6 w-6 {{ $isConnecting ? 'hidden' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 7v5h-5M4 17v-5h5"/>
                <path d="M5.5 9A7 7 0 0 1 18 6l2 2M4 16l2 2a7 7 0 0 0 12.5-3"/>
            </svg>
            <svg data-wifi-icon aria-hidden="true" class="h-6 w-6 {{ $connectionState === 'terputus' ? '' : 'hidden' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 9.5a10.5 10.5 0 0 1 14 0M8 12.5a6 6 0 0 1 8 0M11 15.5a1.5 1.5 0 0 1 2 0"/>
                <path d="M12 19h.01"/>
            </svg>
            <span data-action-label>{{ $koneksi['label_tombol'] }}</span>
        </button>
    </div>

    <script>
        const statusPanel = document.querySelector('[data-connection-status]');
        const infoPanel = document.querySelector('[data-connection-info]');
        const statusTitle = document.querySelector('[data-status-title]');
        const statusDescription = document.querySelector('[data-status-description]');
        const infoMessage = document.querySelector('[data-info-message]');
        const actionButton = document.querySelector('[data-check-connection]');
        const actionLabel = document.querySelector('[data-action-label]');
        const retryIcon = document.querySelector('[data-retry-icon]');
        const wifiIcon = document.querySelector('[data-wifi-icon]');
        const ipInput = document.getElementById('device-ip');
        const editIpButton = document.querySelector('[data-edit-ip]');
        const ipField = document.querySelector('[data-ip-field]');
        const ipError = document.getElementById('device-ip-error');
        const editIcon = editIpButton.querySelector('[data-edit-icon]');
        const confirmIcon = editIpButton.querySelector('[data-confirm-icon]');
        const initialIp = ipInput.value;
        const initialStatus = @json($connectionState);
        const states = @json($connectionStates);

        const updateStatus = (status) => {
            const state = states[status];
            const connecting = status === 'menghubungkan';
            const connected = status === 'terhubung';
            const disconnected = status === 'terputus';
            const statusColors = ['bg-primary-soft', 'bg-accent-amber-soft', 'bg-accent-red-soft'];
            const textColors = ['text-primary', 'text-accent-amber', 'text-accent-red'];
            const activeColor = connected ? 0 : (connecting ? 1 : 2);

            statusPanel.classList.remove(...statusColors, ...textColors);
            statusPanel.classList.add(statusColors[activeColor], textColors[activeColor]);
            infoPanel.classList.remove(...statusColors, ...textColors);
            infoPanel.classList.add(statusColors[activeColor], textColors[activeColor]);
            statusTitle.textContent = state.title;
            statusDescription.textContent = state.description;
            infoMessage.textContent = state.info;
            actionLabel.textContent = state.action;
            actionButton.disabled = connecting;
            actionButton.classList.remove('bg-primary', 'bg-primary-soft', 'bg-border', 'text-surface', 'text-primary', 'text-muted', 'hover:bg-primary/90', 'hover:bg-primary/15', 'cursor-not-allowed');

            if (connecting) {
                actionButton.classList.add('bg-border', 'text-muted', 'cursor-not-allowed');
            } else if (disconnected) {
                actionButton.classList.add('bg-primary', 'text-surface', 'hover:bg-primary/90');
            } else {
                actionButton.classList.add('bg-primary-soft', 'text-primary', 'hover:bg-primary/15');
            }

            retryIcon.classList.toggle('hidden', connecting);
            wifiIcon.classList.toggle('hidden', !disconnected);
            document.querySelectorAll('[data-status-icon]').forEach((icon) => {
                icon.classList.toggle('hidden', icon.dataset.statusIcon !== status);
            });

            let retryRow = document.querySelector('[data-retry-row]');
            if (connecting && !retryRow) {
                retryRow = document.createElement('div');
                retryRow.dataset.retryRow = '';
                retryRow.className = 'flex items-start justify-between gap-4';
                retryRow.innerHTML = '<dt class="text-muted">Percobaan</dt><dd class="text-right font-semibold">2 dari 5</dd>';
                document.querySelector('[aria-labelledby="device-network-title"] dl').append(retryRow);
            } else if (!connecting && retryRow) {
                retryRow.remove();
            }
        };

        const confirmIpEdit = () => {
            const validIp = /^(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)\.){3}(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)$/.test(ipInput.value.trim());

            if (!validIp) {
                ipError.classList.remove('hidden');
                ipField.classList.remove('border-primary', 'ring-primary/20');
                ipField.classList.add('border-accent-red', 'ring-2', 'ring-accent-red/20');
                ipInput.setAttribute('aria-invalid', 'true');
                ipInput.setAttribute('aria-describedby', 'device-ip-error');
                ipInput.focus();
                return;
            }

            // Penyimpanan alamat IP ke server akan ditambahkan pada tahap backend.
            ipInput.value = ipInput.value.trim();
            ipInput.readOnly = true;
            ipInput.removeAttribute('aria-invalid');
            ipInput.removeAttribute('aria-describedby');
            ipError.classList.add('hidden');
            ipField.classList.remove('border-primary', 'border-accent-red', 'ring-2', 'ring-primary/20', 'ring-accent-red/20');
            ipField.classList.add('border-border');
            editIcon.classList.remove('hidden');
            confirmIcon.classList.add('hidden');
            editIpButton.setAttribute('aria-label', 'Ubah alamat IP');
            editIpButton.setAttribute('aria-pressed', 'false');
        };

        const cancelIpEdit = () => {
            ipInput.value = ipInput.dataset.originalValue ?? initialIp;
            ipInput.readOnly = true;
            ipInput.removeAttribute('aria-invalid');
            ipInput.removeAttribute('aria-describedby');
            ipError.classList.add('hidden');
            ipField.classList.remove('border-primary', 'border-accent-red', 'ring-2', 'ring-primary/20', 'ring-accent-red/20');
            ipField.classList.add('border-border');
            editIcon.classList.remove('hidden');
            confirmIcon.classList.add('hidden');
            editIpButton.setAttribute('aria-label', 'Ubah alamat IP');
            editIpButton.setAttribute('aria-pressed', 'false');
        };

        editIpButton.addEventListener('click', () => {
            if (!ipInput.readOnly) {
                confirmIpEdit();
                return;
            }

            ipInput.dataset.originalValue = ipInput.value;
            ipInput.readOnly = false;
            ipError.classList.add('hidden');
            ipInput.removeAttribute('aria-invalid');
            ipInput.removeAttribute('aria-describedby');
            ipField.classList.remove('border-border', 'border-accent-red', 'ring-accent-red/20');
            ipField.classList.add('border-primary', 'ring-2', 'ring-primary/20');
            editIcon.classList.add('hidden');
            confirmIcon.classList.remove('hidden');
            editIpButton.setAttribute('aria-label', 'Konfirmasi alamat IP');
            editIpButton.setAttribute('aria-pressed', 'true');
            ipInput.focus();
            ipInput.select();
        });

        ipInput.addEventListener('keydown', (event) => {
            if (ipInput.readOnly) {
                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                confirmIpEdit();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                cancelIpEdit();
                editIpButton.focus();
            }
        });

        actionButton.addEventListener('click', () => {
            if (actionButton.disabled) {
                return;
            }

            // Simulasi sementara; nanti diganti dengan pengecekan koneksi sungguhan.
            updateStatus('menghubungkan');
            window.setTimeout(() => updateStatus(initialStatus), 2000);
        });
    </script>
@endsection

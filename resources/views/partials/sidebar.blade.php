<aside class="fixed inset-y-0 left-0 hidden w-64 border-r border-border bg-surface px-5 py-8 lg:block">
    <nav aria-label="Navigasi utama" class="space-y-2">
        <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif @class(['flex items-center gap-3 rounded-2xl px-4 py-3', 'bg-primary-soft font-semibold text-primary' => request()->routeIs('dashboard'), 'text-muted transition hover:bg-background hover:text-primary-dark' => ! request()->routeIs('dashboard')])>
            <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10.8 12 3l9 7.8v9.7a.5.5 0 0 1-.5.5h-17a.5.5 0 0 1-.5-.5z"/>
                <path d="M9 21v-8h6v8"/>
            </svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('kontrol') }}" @if (request()->routeIs('kontrol')) aria-current="page" @endif @class(['flex items-center gap-3 rounded-2xl px-4 py-3', 'bg-primary-soft font-semibold text-primary' => request()->routeIs('kontrol'), 'text-muted transition hover:bg-background hover:text-primary-dark' => ! request()->routeIs('kontrol')])>
            <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                <path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/>
                <path d="M15 4v4M9 10v4M17 16v4"/>
            </svg>
            <span>Kontrol</span>
        </a>
        <a href="{{ route('jadwal') }}" @if (request()->routeIs('jadwal')) aria-current="page" @endif @class(['flex items-center gap-3 rounded-2xl px-4 py-3', 'bg-primary-soft font-semibold text-primary' => request()->routeIs('jadwal'), 'text-muted transition hover:bg-background hover:text-primary-dark' => ! request()->routeIs('jadwal')])>
            <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="16" rx="2"/>
                <path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01M16 17h.01"/>
            </svg>
            <span>Jadwal</span>
        </a>
        <a href="#statistik" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-muted transition hover:bg-background hover:text-primary-dark">
            <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 20V14M8 20V9M13 20v-5M18 20V7M23 20V3"/>
                <path d="m3 10 5-5 5 4 8-7"/>
            </svg>
            <span>Statistik</span>
        </a>
    </nav>
</aside>

<nav aria-label="Navigasi utama" class="fixed inset-x-0 bottom-0 z-10 border-t border-border bg-surface px-2 pb-[env(safe-area-inset-bottom)] pt-2 lg:hidden">
    <div class="mx-auto grid max-w-lg grid-cols-4">
        <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif class="flex flex-col items-center gap-1 pb-2 {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted' }}">
            <span @class(['flex h-10 w-[5.5rem] items-center justify-center rounded-full' => true, 'bg-primary-soft' => request()->routeIs('dashboard')])>
                <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.8 12 3l9 7.8v9.7a.5.5 0 0 1-.5.5h-17a.5.5 0 0 1-.5-.5z"/>
                    <path d="M9 21v-8h6v8"/>
                </svg>
            </span>
            <span @class(['text-sm' => true, 'font-semibold' => request()->routeIs('dashboard')])>Beranda</span>
        </a>
        <a href="{{ route('kontrol') }}" @if (request()->routeIs('kontrol')) aria-current="page" @endif class="flex flex-col items-center gap-1 pb-2 {{ request()->routeIs('kontrol') ? 'text-primary' : 'text-muted' }}">
            <span @class(['flex h-10 w-[5.5rem] items-center justify-center rounded-full' => true, 'bg-primary-soft' => request()->routeIs('kontrol')])>
                <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/>
                    <path d="M15 4v4M9 10v4M17 16v4"/>
                </svg>
            </span>
            <span @class(['text-sm' => true, 'font-semibold' => request()->routeIs('kontrol')])>Kontrol</span>
        </a>
        <a href="#jadwal" class="flex flex-col items-center gap-1 pb-2 text-muted">
            <span class="flex h-10 w-[5.5rem] items-center justify-center">
                <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01M16 17h.01"/>
                </svg>
            </span>
            <span class="text-sm">Jadwal</span>
        </a>
        <a href="#statistik" class="flex flex-col items-center gap-1 pb-2 text-muted">
            <span class="flex h-10 w-[5.5rem] items-center justify-center">
                <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 20V14M8 20V9M13 20v-5M18 20V7M23 20V3"/>
                    <path d="m3 10 5-5 5 4 8-7"/>
                </svg>
            </span>
            <span class="text-sm">Statistik</span>
        </a>
    </div>
</nav>
